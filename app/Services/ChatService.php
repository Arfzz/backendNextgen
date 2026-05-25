<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class ChatService
{
    /**
     * Dapatkan semua conversation milik user tertentu (Student atau Mentor).
     */
    public function getConversations(User $user)
    {
        $roleField = $user->role === 'student' ? 'student_id' : 'mentor_id';

        return Conversation::with(['student', 'mentor'])
            ->where($roleField, (string) $user->_id)
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * Ambil histori pesan di dalam conversation.
     * Otomatis membuat conversation baru jika belum ada dan requested by Student.
     */
    public function getConversationMessages(User $user, string $targetUserId)
    {
        $studentId = $user->role === 'student' ? (string) $user->_id : $targetUserId;
        $mentorId  = $user->role === 'mentor'  ? (string) $user->_id : $targetUserId;

        $conversation = Conversation::firstOrCreate(
            ['student_id' => $studentId, 'mentor_id' => $mentorId],
            ['last_message' => null, 'last_message_at' => null]
        );

        // Tandai pesan sebagai sudah dibaca jika lawan bicara yang membuka
        Message::where('conversation_id', (string) $conversation->_id)
            ->where('sender_id', '!=', (string) $user->_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return [
            'conversation' => $conversation->load(['student', 'mentor']),
            'messages'     => $conversation->messages()->orderBy('created_at', 'asc')->get()
        ];
    }

    /**
     * Kirim pesan baru ke target user (Student/Mentor).
     */
    public function sendMessage(User $sender, string $targetUserId, string $content)
    {
        $studentId = $sender->role === 'student' ? (string) $sender->_id : $targetUserId;
        $mentorId  = $sender->role === 'mentor'  ? (string) $sender->_id : $targetUserId;

        $conversation = Conversation::firstOrCreate(
            ['student_id' => $studentId, 'mentor_id' => $mentorId],
            ['last_message' => null, 'last_message_at' => null]
        );

        $message = Message::create([
            'conversation_id' => (string) $conversation->_id,
            'sender_id'       => (string) $sender->_id,
            'content'         => $content,
            'is_read'         => false,
        ]);

        $conversation->update([
            'last_message'    => $content,
            'last_message_at' => now(),
        ]);

        return $message->load('sender');
    }
}
