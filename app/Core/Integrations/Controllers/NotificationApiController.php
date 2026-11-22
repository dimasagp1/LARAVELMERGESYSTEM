<?php

namespace App\Core\Integrations\Controllers;

use App\Core\Integrations\Services\NotificationCenterService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function __construct(
        protected NotificationCenterService $notificationService
    ) {}

    /**
     * Get recent notifications and unread count.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->getNotificationsForUser($user, 10);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $unreadCount,
            'total' => $notifications->total(),
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notifikasi telah dibaca' : 'Notifikasi tidak ditemukan',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'message' => "Semua ({$count}) notifikasi ditandai sebagai telah dibaca",
        ]);
    }
}
