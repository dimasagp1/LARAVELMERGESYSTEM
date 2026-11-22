<?php

namespace App\Core\Integrations\Services;

use App\Core\Integrations\Models\ErpNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationCenterService
{
    /**
     * Send an ERP notification.
     *
     * @param  array<string, mixed>|null  $data
     */
    public function send(
        string $moduleSlug,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $urgency = 'normal',
        ?int $userId = null,
        ?array $data = null
    ): ErpNotification {
        return ErpNotification::create([
            'user_id' => $userId,
            'module_slug' => $moduleSlug,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'urgency' => $urgency,
            'data_json' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Broadcast notification to all users with a specific role or all active users.
     *
     * @param  array<string, mixed>|null  $data
     * @return Collection<int, ErpNotification>
     */
    public function broadcast(
        string $moduleSlug,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $urgency = 'normal',
        ?array $data = null
    ): Collection {
        // Send a single system-wide notification with user_id = null
        $notification = $this->send(
            moduleSlug: $moduleSlug,
            type: $type,
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            urgency: $urgency,
            userId: null,
            data: $data
        );

        return new Collection([$notification]);
    }

    /**
     * Get paginated notifications for active user.
     *
     * @return LengthAwarePaginator<ErpNotification>
     */
    public function getNotificationsForUser(?User $user, int $perPage = 15): LengthAwarePaginator
    {
        $userId = $user?->id;

        return ErpNotification::query()
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get unread notifications count for active user.
     */
    public function getUnreadCount(?User $user): int
    {
        $userId = $user?->id;

        return ErpNotification::query()
            ->where('is_read', false)
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->count();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = ErpNotification::find($notificationId);
        if (! $notification) {
            return false;
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(?User $user): int
    {
        $userId = $user?->id;

        return ErpNotification::query()
            ->where('is_read', false)
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
