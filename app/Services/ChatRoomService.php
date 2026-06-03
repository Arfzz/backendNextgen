<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * ChatRoomService
 *
 * Auto-provisions:
 *   - Group rooms:   1 per beasiswa (all students + mentor)
 *   - Private rooms: student ↔ mentor (provisioned on first load)
 */
class ChatRoomService
{
    // ─────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────

    public function getRoomsForUser(Authenticatable $auth): Collection
    {
        $userId    = (string) $auth->getAuthIdentifier();
        $beasiswas = $this->normalizeArray($auth->beasiswa_diampu ?? []);

        // 1. Provision group rooms for each beasiswa
        foreach ($beasiswas as $name) {
            $name = trim((string) $name);
            if ($name === '') continue;
            try {
                $this->ensureGroupRoom($name);
            } catch (\Throwable $e) {
                Log::error('ChatRoomService@ensureGroupRoom', ['beasiswa' => $name, 'error' => $e->getMessage()]);
            }
        }

        // 2. Provision private rooms based on role
        try {
            if ($auth instanceof Mentor) {
                $this->ensurePrivateRoomsForMentor($auth, $beasiswas);
            } else {
                $this->ensurePrivateRoomWithMentor($auth, $beasiswas);
            }
        } catch (\Throwable $e) {
            Log::error('ChatRoomService@ensurePrivateRooms', ['error' => $e->getMessage()]);
        }

        // 3. Return all rooms this user participates in
        return ChatRoom::where('participants', $userId)
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->values();
    }

    /**
     * Ensure group chat room exists for a beasiswa.
     */
    public function ensureGroupRoom(string $beasiswaName): ChatRoom
    {
        $room = ChatRoom::where('type', 'group')
            ->where('beasiswa_name', $beasiswaName)
            ->first();

        if ($room) {
            $this->syncGroupParticipants($room, $beasiswaName);
            return $room->fresh();
        }

        return ChatRoom::create([
            'type'            => 'group',
            'beasiswa_name'   => $beasiswaName,
            'name'            => $beasiswaName,
            'participants'    => array_values($this->getParticipantsForBeasiswa($beasiswaName)),
            'last_message'    => '',
            'last_message_at' => now(),
        ]);
    }

    /**
     * Open (or retrieve) a private room between two users.
     */
    public function getOrCreatePrivateRoom(Authenticatable $auth, string $targetUserId): ChatRoom
    {
        $userId = (string) $auth->getAuthIdentifier();
        $ids    = [$userId, $targetUserId];
        sort($ids);

        $room = $this->findPrivateRoom($ids[0], $ids[1]);
        if ($room) return $room;

        return ChatRoom::create([
            'type'            => 'private',
            'name'            => $this->resolveUserName($targetUserId),
            'participants'    => array_values($ids),
            'last_message'    => '',
            'last_message_at' => now(),
        ]);
    }

    public function getMessages(string $roomId, int $limit = 80): Collection
    {
        return ChatMessage::where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function sendMessage(Authenticatable $auth, string $roomId, string $content): ?ChatMessage
    {
        $room = ChatRoom::find($roomId);
        if (! $room) return null;

        $senderId     = (string) $auth->getAuthIdentifier();
        $participants = $this->normalizeArray($room->participants ?? []);

        if (! in_array($senderId, $participants, true)) return null;

        $msg = ChatMessage::create([
            'room_id'    => $roomId,
            'sender_id'  => $senderId,
            'content'    => $content,
            'is_read_by' => array_values([$senderId]), // ← real BSON array, not JSON string
        ]);

        $room->update([
            'last_message'    => $content,
            'last_message_at' => now(),
        ]);

        return $msg;
    }

    /**
     * Mark all messages in a room as read by the given viewer.
     * Called when a user opens a chat room.
     */
    public function markRoomAsRead(string $roomId, string $viewerId): void
    {
        // Find messages not sent by viewer that haven't been read by viewer yet.
        // We handle both BSON array format and legacy JSON-string format via regex.
        $messages = ChatMessage::where('room_id', $roomId)
            ->where('sender_id', '!=', $viewerId)
            ->where('is_read_by', 'not regex', "/{$viewerId}/") // works for both formats
            ->get();

        foreach ($messages as $msg) {
            $readBy = $this->normalizeArray($msg->is_read_by ?? []);
            if (! in_array($viewerId, $readBy, true)) {
                $readBy[] = $viewerId;
                $msg->update(['is_read_by' => array_values($readBy)]);
            }
        }
    }

    /**
     * Count unread messages for viewer in a room.
     * Uses regex to handle both BSON array and legacy JSON-string formats.
     */
    public function countUnread(string $roomId, string $viewerId): int
    {
        return ChatMessage::where('room_id', $roomId)
            ->where('sender_id', '!=', $viewerId)
            ->where('is_read_by', 'not regex', "/{$viewerId}/")
            ->count();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private room provisioning
    // ─────────────────────────────────────────────────────────────────────

    /**
     * For STUDENT: ensure 1 private room exists with their mentor.
     */
    private function ensurePrivateRoomWithMentor(Authenticatable $student, array $beasiswas): void
    {
        $studentId = (string) $student->getAuthIdentifier();

        foreach ($beasiswas as $beasiswaName) {
            $escaped = preg_quote(trim((string) $beasiswaName), '/');
            $mentor  = Mentor::where('beasiswa_diampu', 'regex', "/{$escaped}/i")->first();

            if (! $mentor) continue;

            $mentorId = (string) $mentor->getKey();
            $ids      = [$studentId, $mentorId];
            sort($ids);

            if (! $this->findPrivateRoom($ids[0], $ids[1])) {
                ChatRoom::create([
                    'type'            => 'private',
                    'name'            => $mentor->name, // uses getNameAttribute()
                    'participants'    => array_values($ids),
                    'last_message'    => '',
                    'last_message_at' => now(),
                ]);

                Log::info('ChatRoomService: provisioned student↔mentor private room', [
                    'student' => $studentId,
                    'mentor'  => $mentorId,
                ]);
            }
        }
    }

    /**
     * For MENTOR: ensure a private room exists with every student in their beasiswas.
     */
    private function ensurePrivateRoomsForMentor(Authenticatable $mentor, array $beasiswas): void
    {
        $mentorId = (string) $mentor->getAuthIdentifier();

        foreach ($beasiswas as $beasiswaName) {
            $escaped    = preg_quote(trim((string) $beasiswaName), '/');
            $pattern    = "/{$escaped}/i";

            $students = User::where('role', UserRole::Student->value)
                ->where('beasiswa_diampu', 'regex', $pattern)
                ->get()
                ->filter(fn ($u) => strlen((string) $u->getKey()) > 5);

            foreach ($students as $student) {
                $studentId = (string) $student->getKey();
                $ids       = [$mentorId, $studentId];
                sort($ids);

                if (! $this->findPrivateRoom($ids[0], $ids[1])) {
                    ChatRoom::create([
                        'type'            => 'private',
                        'name'            => $student->name ?? 'Student',
                        'participants'    => array_values($ids),
                        'last_message'    => '',
                        'last_message_at' => now(),
                    ]);

                    Log::info('ChatRoomService: provisioned mentor↔student private room', [
                        'mentor'  => $mentorId,
                        'student' => $studentId,
                    ]);
                }
            }
        }
    }

    /**
     * Find an existing private room between exactly two users.
     */
    private function findPrivateRoom(string $idA, string $idB): ?ChatRoom
    {
        // Sort IDs so lookup is order-independent
        $ids = [$idA, $idB];
        sort($ids);

        return ChatRoom::where('type', 'private')
            ->where('participants', 'all', $ids)
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Group room helpers
    // ─────────────────────────────────────────────────────────────────────

    private function syncGroupParticipants(ChatRoom $room, string $beasiswaName): void
    {
        $current = $this->normalizeArray($room->participants ?? []);
        $latest  = $this->getParticipantsForBeasiswa($beasiswaName);
        $newIds  = array_diff($latest, $current);

        if (! empty($newIds)) {
            $merged = array_values(array_unique(array_merge($current, $newIds)));
            $room->update(['participants' => $merged]);
        }
    }

    /**
     * Get all participant IDs (students + mentor) for a beasiswa.
     * Uses regex to handle both real-array and JSON-string storage formats.
     */
    private function getParticipantsForBeasiswa(string $beasiswaName): array
    {
        $escaped = preg_quote($beasiswaName, '/');
        $pattern = "/{$escaped}/i";

        $studentIds = User::where('role', UserRole::Student->value)
            ->where('beasiswa_diampu', 'regex', $pattern)
            ->get()
            ->map(fn ($u) => (string) $u->getKey())
            ->filter(fn ($id) => strlen($id) > 5)
            ->values()
            ->toArray();

        $mentorIds = Mentor::where('beasiswa_diampu', 'regex', $pattern)
            ->get()
            ->map(fn ($m) => (string) $m->getKey())
            ->filter(fn ($id) => strlen($id) > 5)
            ->values()
            ->toArray();

        $all = array_values(array_unique(array_merge($studentIds, $mentorIds)));

        Log::info('ChatRoomService@getParticipantsForBeasiswa', [
            'beasiswa'   => $beasiswaName,
            'studentIds' => $studentIds,
            'mentorIds'  => $mentorIds,
            'total'      => count($all),
        ]);

        return $all;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Utility (used by ChatController)
    // ─────────────────────────────────────────────────────────────────────

    public function resolveUserName(string $userId): string
    {
        $user = User::find($userId);
        if ($user) return $user->name ?? 'Pengguna';

        $mentor = Mentor::find($userId);
        if ($mentor) return $mentor->name; // getNameAttribute()

        return 'Pengguna';
    }

    public function resolveUserRole(string $userId): string
    {
        if (User::find($userId)) return 'student';
        if (Mentor::find($userId)) return 'mentor';
        return '';
    }

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value)));
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if (str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('strval', $decoded)));
                }
            }
            return $trimmed !== '' ? [$trimmed] : [];
        }
        return [];
    }
}
