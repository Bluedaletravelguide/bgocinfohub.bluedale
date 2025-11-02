<?php

namespace App\Support\Notifications;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryLogger
{
    /**
     * Check if notification should be debounced (skipped due to recent send)
     */
    public function shouldDebounce(int $userId, int $itemId, string $channel, string $event, int $minutes = 5): bool
    {
        $key = $this->getCacheKey($userId, $itemId, $channel, $event);
        return Cache::has($key);
    }

    /**
     * Log that a notification was sent
     */
    public function logSent(int $userId, int $itemId, string $channel, string $event, int $minutes = 5): void
    {
        $key = $this->getCacheKey($userId, $itemId, $channel, $event);
        Cache::put($key, true, now()->addMinutes($minutes));

        Log::info('Notification sent', [
            'user_id' => $userId,
            'item_id' => $itemId,
            'channel' => $channel,
            'event' => $event,
        ]);
    }

    /**
     * Log that a notification was skipped
     */
    public function logSkipped(int $userId, int $itemId, string $channel, string $event, array $metadata = []): void
    {
        Log::info('Notification skipped', array_merge([
            'user_id' => $userId,
            'item_id' => $itemId,
            'channel' => $channel,
            'event' => $event,
        ], $metadata));
    }

    /**
     * Generate cache key for debouncing
     */
    protected function getCacheKey(int $userId, int $itemId, string $channel, string $event): string
    {
        return "notification:{$userId}:{$itemId}:{$channel}:{$event}";
    }
}