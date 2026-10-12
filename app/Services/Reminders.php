<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\MonitoringLog;
use App\Models\Notice;
use App\Models\User;

/**
 * The two daily nudges for workers: "daily check due" and "ready to harvest".
 * One short digest per worker (not one message per batch) so a text message stays a single SMS.
 * Same wording everywhere, so Notice::once() never sends the same reminder twice in one day.
 */
class Reminders
{
    /** @return int how many reminders were created */
    public static function forWorker(User $worker, bool $external = false): int
    {
        $fruiting = Batch::where('status', 'fruiting')->orderBy('code')->get();
        if ($fruiting->isEmpty()) return 0;

        $checked  = MonitoringLog::whereDate('created_at', today())->pluck('batch_id')->all();
        $due      = $fruiting->reject(fn ($b) => in_array($b->id, $checked));
        $ready    = $fruiting->filter(fn ($b) => $b->harvest_date && $b->harvest_date->lte(today()));
        $sent     = 0;

        if ($due->isNotEmpty()) {
            Notice::once($worker->id, 'Daily check due', self::sentence($due, 'no check today'), route('worker.monitoring', [], false), $external);
            $sent++;
        }
        if ($ready->isNotEmpty()) {
            Notice::once($worker->id, 'Ready to harvest', self::sentence($ready, 'reached the estimated harvest date'), route('worker.harvest', [], false), $external);
            $sent++;
        }

        return $sent;
    }

    private static function sentence($batches, string $what): string
    {
        $codes = $batches->pluck('code');
        $list  = $codes->take(5)->implode(', ').($codes->count() > 5 ? ' and '.($codes->count() - 5).' more' : '');

        return $batches->count() === 1
            ? "Batch {$list} has {$what}."
            : "{$batches->count()} batches: {$list} - {$what}.";
    }
}
