<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Mentor;
use App\Models\User;
use App\Services\ChatRoomService;
use App\Services\NotificationService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(private readonly ChatRoomService $chatService) {}

    // ─── GET /v1/chat/rooms ──────────────────────────────────────────────────
    public function rooms(Request $request): JsonResponse
    {
        $auth = $request->user();

        try {
            $rooms = $this->chatService->getRoomsForUser($auth);
            $data  = $rooms->map(fn ($room) => $this->formatRoom($room, $auth))->values();

            return response()->json(['rooms' => $data]);
        } catch (\Throwable $e) {
            Log::error('ChatController@rooms', [
                'user'  => (string) $auth->getAuthIdentifier(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['rooms' => [], 'debug' => $e->getMessage()], 500);
        }
    }

    // ─── POST /v1/chat/rooms/private/{targetUserId} ──────────────────────────
    public function openPrivate(Request $request, string $targetUserId): JsonResponse
    {
        try {
            $room = $this->chatService->getOrCreatePrivateRoom($request->user(), $targetUserId);
            return response()->json(['room' => $this->formatRoom($room, $request->user())]);
        } catch (\Throwable $e) {
            Log::error('ChatController@openPrivate', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ─── GET /v1/chat/rooms/{roomId}/messages ───────────────────────────────
    public function messages(Request $request, string $roomId): JsonResponse
    {
        $auth = $request->user();

        try {
            // Mark all messages in this room as read by the viewer
            $this->chatService->markRoomAsRead($roomId, (string) $auth->getAuthIdentifier());

            $messages = $this->chatService->getMessages($roomId);

            $data = $messages->map(fn ($m) => [
                'id'         => (string) $m->_id,
                'room_id'    => $m->room_id,
                'sender_id'  => $m->sender_id,
                'content'    => $m->content,
                'is_read_by' => $m->is_read_by ?? [],
                'created_at' => $m->created_at?->toIso8601String(),
                'sender'     => $this->senderInfo($m->sender_id),
            ])->values();

            return response()->json(['messages' => $data]);
        } catch (\Throwable $e) {
            Log::error('ChatController@messages', ['room_id' => $roomId, 'error' => $e->getMessage()]);
            return response()->json(['messages' => [], 'debug' => $e->getMessage()], 500);
        }
    }

    // ─── POST /v1/chat/rooms/{roomId}/messages ──────────────────────────────
    public function sendMessage(Request $request, string $roomId): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:2000']);

        try {
            $msg = $this->chatService->sendMessage(
                $request->user(),
                $roomId,
                $request->input('content')
            );

            if (! $msg) {
                return response()->json(['message' => 'Room tidak ditemukan atau akses ditolak.'], 403);
            }

            // Notify all other participants in the room
            try {
                $room = ChatRoom::find($roomId);
                if ($room) {
                    $senderId    = (string) $request->user()->getAuthIdentifier();
                    $senderName  = $this->chatService->resolveUserName($senderId);
                    $roomName    = $room->type === 'group'
                        ? ($room->name ?? 'Grup Chat')
                        : 'Pesan Pribadi';
                    $preview     = mb_strimwidth($request->input('content'), 0, 60, '...');
                    $participants = $room->participants ?? [];

                    foreach ($participants as $participantId) {
                        if ((string) $participantId === $senderId) continue;
                        NotificationService::newChat(
                            (string) $participantId,
                            $senderName,
                            $roomName,
                            $preview,
                            $roomId
                        );
                    }
                }
            } catch (\Throwable) {}

            return response()->json([
                'message' => 'Pesan terkirim.',
                'data'    => [
                    'id'         => (string) $msg->_id,
                    'room_id'    => $msg->room_id,
                    'sender_id'  => $msg->sender_id,
                    'content'    => $msg->content,
                    'created_at' => $msg->created_at?->toIso8601String(),
                    'sender'     => $this->senderInfo($msg->sender_id),
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatController@sendMessage', ['room_id' => $roomId, 'error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function formatRoom($room, Authenticatable $viewer): array
    {
        $viewerId    = (string) $viewer->getAuthIdentifier();
        $displayName = $room->name ?? '';

        // Private rooms: show the other person's name
        if ($room->type === 'private') {
            $participants = $room->participants ?? [];
            $otherId = collect($participants)->first(fn ($id) => $id !== $viewerId);
            if ($otherId) {
                $displayName = $this->chatService->resolveUserName($otherId);
            }
        }

        // Count unread — uses regex to handle both real BSON array and legacy JSON-string
        $roomId      = (string) $room->_id;
        $unreadCount = $this->chatService->countUnread($roomId, $viewerId);

        return [
            'id'              => $roomId,
            'type'            => $room->type,
            'name'            => $displayName,
            'beasiswa_name'   => $room->beasiswa_name,
            'last_message'    => $room->last_message ?? '',
            'last_message_at' => $room->last_message_at?->toIso8601String() ?? '',
            'participants'    => $room->participants ?? [],
            'unread_count'    => $unreadCount,
        ];
    }

    private function senderInfo(string $senderId): array
    {
        return [
            'id'   => $senderId,
            'name' => $this->chatService->resolveUserName($senderId),
            'role' => $this->chatService->resolveUserRole($senderId),
        ];
    }
}
