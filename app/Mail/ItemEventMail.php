<?php 

namespace App\Mail;

use App\Models\Item;
use App\Support\Notifications\ItemEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $event,
        public Item $item,
        public array $ctx = []
    ) {}

    public function build()
    {
        $subject = match ($this->event) {
            ItemEvent::CREATED          => "🆕 [BGOC] New Task: {$this->item->task}",
            ItemEvent::STATUS_CHANGED   => "🔄 [BGOC] Status → {$this->item->status}: {$this->item->task}",
            ItemEvent::ASSIGNEE_CHANGED => "👤 [BGOC] You’ve been assigned: {$this->item->task}",
            default                     => "[BGOC] Update: {$this->item->task}",
        };

        return $this->subject($subject)
            ->view('emails.item_event')
            ->with([
                'event' => $this->event,
                'item'  => $this->item,
                'ctx'   => $this->ctx,
            ]);
    }
}
