<?php

namespace App\Support\Notifications;

use App\Models\Item;
use App\Models\User;
use App\Models\RecipientMapping;

class RecipientResolver
{
    /**
     * @return User[]
     */
    public function resolveForItem(Item $item, string $event, array $ctx = []): array
    {
        $users = [];

        if ($item->assign_to_id) {
            if ($u = $this->resolveTextToUser($item->assign_to_id)) {
                $users[$u->id] = $u;
            }
        }

        if ($event === ItemEvent::ASSIGNEE_CHANGED && !empty($ctx['old_assign_to'])) {
            if ($old = $this->resolveTextToUser($ctx['old_assign_to'])) {
                $users[$old->id] = $old;
            }
        }

        if (empty($users)) {
            if (!empty($item->created_by) && $creator = User::find($item->created_by, ['*'])) {
                $users[$creator->id] = $creator;
            }
            foreach ($this->adminUsers() as $admin) {
                $users[$admin->id] = $admin;
            }
        }

        return array_values($users);
    }

    protected function resolveTextToUser(string $raw): ?User
    {
        $key = $this->normalize($raw);

        if (str_contains($key, '@')) {
            return User::whereRaw('LOWER(email) = ?', [$key])->first();
        }

        $map = RecipientMapping::where('key', $key)->first();
        if ($map && $u = User::find($map->user_id, ['*'])) {
            return $u;
        }

        $u = User::whereRaw('LOWER(name) = ?', [$key])->first();
        if ($u) {
            return $u;
        }

        $cands = User::whereRaw('LOWER(name) LIKE ?', ["%{$key}%"])->get();
        return $cands->count() === 1 ? $cands->first() : null;
    }

    protected function normalize(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/[^a-z0-9@.\s]/', '', $s);
        return $s;
    }

    /**
     * @return User[]
     */
    protected function adminUsers(): array
    {
        return User::where('role', '=', 'admin')->get()->all();
    }
}