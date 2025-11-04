<?php

namespace App\Jobs;

use App\Mail\ItemEventMail;
use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// ✅ Use the *singular* namespace that exists in your project
use App\Support\Notifications\RecipientResolver;
use App\Support\Notifications\DeliveryLogger;
use App\Support\Notifications\ItemEvent;

class SendItemNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retries & backoff */
    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(
        public string $event,
        public int $itemId,
        public array $context = []
    ) {}

    public function handle(): void
    {
        try {
            $item = Item::find($this->itemId, ['*']);
            if (!$item) {
                Log::warning('SendItemNotificationJob: Item not found', [
                    'item_id' => $this->itemId,
                    'event'   => $this->event,
                ]);
                return;
            }

            // Resolve optional helpers from container, but don't crash if missing
            $resolver = $this->makeOrNull(RecipientResolver::class);
            $logger   = $this->makeOrNull(DeliveryLogger::class) ?? $this->nullLogger();

            // Decide recipients
            $recipients = [];
            if ($resolver) {
                $recipients = match ($this->event) {
                    ItemEvent::CREATED,
                    ItemEvent::STATUS_CHANGED,
                    ItemEvent::ASSIGNEE_CHANGED => $resolver->resolveForItem($item, $this->event, $this->context),
                    default => [],
                };
            } else {
                // Fallback: try from context if resolver class not available
                $recipients = $this->context['recipients'] ?? [];
            }

            if (empty($recipients)) {
                Log::info('SendItemNotificationJob: No recipients', [
                    'item_id' => $item->id,
                    'event'   => $this->event,
                ]);
                return;
            }

            foreach ($recipients as $user) {
                $userId = is_object($user) ? ($user->id ?? null) : null;
                $email  = is_object($user) ? ($user->email ?? null) : (is_string($user) ? $user : null);

                if (!$email) {
                    Log::warning('SendItemNotificationJob: recipient missing email', [
                        'item_id' => $item->id,
                        'user'    => $user,
                    ]);
                    continue;
                }

                if (method_exists($logger, 'shouldDebounce') &&
                    $logger->shouldDebounce($userId, $item->id, 'mail', $this->event)) {
                    $logger->logSkipped($userId, $item->id, 'mail', $this->event, ['reason' => 'debounced']);
                    continue;
                }

                // Send the mail
                Mail::to($email)->send(new ItemEventMail($this->event, $item, $this->context));

                if (method_exists($logger, 'logSent')) {
                    $logger->logSent($userId, $item->id, 'mail', $this->event);
                }
            }
        } catch (\Throwable $e) {
            Log::error('SendItemNotificationJob failed', [
                'item_id' => $this->itemId,
                'event'   => $this->event,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e; // keep for retries/backoff
        }
    }

    /**
     * Try to make a class from the container without throwing if it doesn't exist.
     */
    protected function makeOrNull(string $class): mixed
    {
        try {
            if (class_exists($class)) {
                return app($class);
            }
        } catch (\Throwable) {}
        return null;
    }

    /**
     * Minimal no-op logger fallback so job never crashes if DeliveryLogger is absent.
     */
    protected function nullLogger(): object
    {
        return new class {
            public function shouldDebounce(...$args): bool { return false; }
            public function logSkipped(...$args): void {}
            public function logSent(...$args): void {}
        };
    }
}
