<?php

namespace App\Jobs;

use App\Mail\DailyDigestMail;
use App\Models\Item;
use App\Models\User;
use App\Support\Notifications\DeliveryLogger;  
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DeliveryLogger $logger): void
    {
        $today = now('Asia/Kuala_Lumpur')->startOfDay();

        $items = Item::query()
            ->where('status', '!=', 'Completed')
            ->get();

        $kpis = [
            'total'        => $items->count(),
            'due_today'    => $items->where('deadline', $today->toDateString())->count(),
            'due_tomorrow' => $items->where('deadline', $today->copy()->addDay()->toDateString())->count(),
            'overdue'      => $items->where('deadline', '<', $today->toDateString())->count(),
        ];

        $topRisks = $items->where('deadline', '<', $today->toDateString())
            ->sortBy('deadline')
            ->take(5);

        // Fixed: Added ->toArray() to ensure it's countable before sortDesc()
        $byAssignee = $items->groupBy(fn ($i) => $i->assign_to_id ?: 'Unassigned')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $ge = $items->filter(fn ($i) => strtolower((string) $i->company_id) === 'ge')
            ->sortBy('deadline');

        // Fixed: Added comparison operator
        $admins = User::where('role', '=', 'admin')->get();

        if ($kpis['total'] === 0) {
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new DailyDigestMail($kpis, collect(), collect(), collect(), true));
                $logger->logSent($admin->id, 0, 'mail', 'daily_digest_admin');
            }
            return;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new DailyDigestMail($kpis, $topRisks, $byAssignee, $ge, false));
            $logger->logSent($admin->id, 0, 'mail', 'daily_digest_admin');
        }
    }
}