<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     * List all notifications for the authenticated user (student or mentor).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', (string) $request->user()->_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(NotificationResource::collection($notifications));
    }

    /**
     * POST /api/v1/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::find($id);

        if (! $notification || (string) $notification->user_id !== (string) $request->user()->_id) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * POST /api/v1/notifications/read-all
     * Mark ALL notifications of the authenticated user as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', (string) $request->user()->_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
