<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class TickScheduler
{
    public function handle($request, Closure $next)
    {
        // kunci 55 detik agar tidak dobel eksekusi
        if (Cache::add('tick-scheduler-lock', 1, 55)) {
            try {
                Artisan::call('schedule:run');
                Artisan::call('queue:work', [
                    '--once'            => true,
                    '--stop-when-empty' => true,
                    '--quiet'           => true,
                ]);
            } finally {
                // biarkan lock habis sendiri (55 dtk)
            }
        }

        return $next($request);
    }
}
