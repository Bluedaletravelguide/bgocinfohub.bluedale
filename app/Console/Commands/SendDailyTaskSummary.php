<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\User;
use App\Mail\DailyTaskSummary;
use Illuminate\Support\Facades\Mail;

class SendDailyTaskSummary extends Command
{
    protected $signature = 'email:daily-task-summary {--to=* : Emails to send to (defaults to all admins)}';
    protected $description = 'Send daily task reminder email with Pending / Expired / In Progress / Completed tables';

    public function handle(): int
    {
        // Fetch all items and order by deadline
        $items = Item::query()->orderBy('deadline')->get();

        // Normalize status value (case-insensitive)
        $normalize = fn($status) => strtolower(trim((string)$status));

        // Group by derived_status or fallback to status column
        $expired = $items->filter(fn($i) => $normalize($i->derived_status ?? $i->status) === 'expired');
        $pending = $items->filter(fn($i) => $normalize($i->derived_status ?? $i->status) === 'pending');
        $inProgress = $items->filter(fn($i) => in_array($normalize($i->derived_status ?? $i->status), ['in progress', 'ongoing']));
        $completed = $items->filter(fn($i) => $normalize($i->derived_status ?? $i->status) === 'completed');

        // Prepare data payload
        $payload = [
            'expired'     => $expired->values()->all(),
            'pending'     => $pending->values()->all(),
            'in_progress' => $inProgress->values()->all(),
            'completed'   => $completed->values()->all(),
        ];

        // Resolve recipient list
        $emails = $this->option('to');
        if (empty($emails)) {
            $emails = User::where('role', '=', 'admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->all();
        }

        // Send mail to each recipient
        foreach ($emails as $email) {
            Mail::to($email)->send(new DailyTaskSummary($payload));
            $this->info("✅ Daily Task Summary sent to {$email}");
        }

        $this->info('All daily task summaries sent successfully.');
        return self::SUCCESS;
    }
}
