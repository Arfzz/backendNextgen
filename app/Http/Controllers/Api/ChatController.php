<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    /**
     * List semua percakapan yang dimiliki oleh User (bisa student/mentor)
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = $this->chatService->getConversations($request->user());

        return response()->json([
            'conversations' => ConversationResource::collection($conversations)
        ]);
    }

    /**
     * Tampilkan detail chat antara Auth User dan Target User
     */
    public function show(Request $request, string $targetUserId): JsonResponse
    {
        $data = $this->chatService->getConversationMessages($request->user(), $targetUserId);

        return response()->json([
            'conversation' => new ConversationResource($data['conversation']),
            'messages'     => MessageResource::collection($data['messages'])
        ]);
    }

    /**
     * Kirim pesan baru ke Target User
     */
    public function store(Request $request, string $targetUserId): JsonResponse
    {
        $request->validate(['content' => 'required|string']);

        $message = $this->chatService->sendMessage(
            $request->user(), 
            $targetUserId, 
            $request->input('content')
        );

        return response()->json([
            'message' => 'Pesan terkirim.',
            'data'    => new MessageResource($message)
        ], 201);
    }
}
