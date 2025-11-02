<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLog extends Model
{
    protected $table = 'delivery_logs';

    protected $fillable = [
        'user_id',
        'item_id',
        'channel',
        'event',
        'status',
        'meta',
        'sent_at',
    ];

    protected $casts = [
        'meta'    => 'array',
        'sent_at' => 'datetime',
    ];
}
