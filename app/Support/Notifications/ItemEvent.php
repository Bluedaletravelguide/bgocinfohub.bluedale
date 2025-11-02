<?php

namespace App\Support\Notifications;

final class ItemEvent
{
    public const CREATED            = 'created';
    public const STATUS_CHANGED     = 'status_changed';
    public const ASSIGNEE_CHANGED   = 'assignee_changed';
    public const DEADLINE_H7        = 'deadline_h7';
    public const DEADLINE_H1        = 'deadline_h1';
    public const DEADLINE_H         = 'deadline_h';
    public const OVERDUE            = 'overdue';
    public const DAILY_DIGEST_ADMIN = 'daily_digest_admin';
    public const DAILY_DIGEST_USER  = 'daily_digest_user';
}
