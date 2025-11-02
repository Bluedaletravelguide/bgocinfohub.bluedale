<?php

namespace App\Observers;

use App\Jobs\SendItemNotificationJob;
use App\Models\Item;
use App\Support\Notifications\ItemEvent;

class ItemObserver
{
    public function created(Item $item): void
    {
        SendItemNotificationJob::dispatch(
            ItemEvent::CREATED,
            $item->id,
            []
        );
    }

    public function updated(Item $item): void
    {
        $changes = [];
        
        if ($item->isDirty('status')) {
            SendItemNotificationJob::dispatch(
                ItemEvent::STATUS_CHANGED,
                $item->id,
                [
                    'old_status' => $item->getOriginal('status'),
                    'new_status' => $item->status,
                ]
            );
        }

        if ($item->isDirty('assign_to_id')) {
            SendItemNotificationJob::dispatch(
                ItemEvent::ASSIGNEE_CHANGED,
                $item->id,
                [
                    'old_assign_to' => $item->getOriginal('assign_to_id'),
                    'new_assign_to' => $item->assign_to_id,
                ]
            );
        }
    }
}