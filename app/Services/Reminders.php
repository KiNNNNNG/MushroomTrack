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
        $ready = $fruiting->filter(fn ($b) => $b->harvest_date && $b->harvest_date->lte(today()));
        $soon  = $fruiting->filter(fn ($b) => $b->harvest_date && $b->harvest_date->gt(today()) && $b->harvest_date->lte(today()->addDays(3)));
        $sent  = 0;

        if ($ready->isNotEmpty()) {
            foreach ($ready as $rb) {
                Notice::once($worker->id, '🌿 Harvest time!', "{$rb->code} is ready to harvest today.", route('worker.harvest', [], false), $external);
                $sent++;
            }
        }

        if ($soon->isNotEmpty()) {
            foreach ($soon as $sb) {
                $days = today()->diffInDays($sb->harvest_date);
                Notice::once($worker->id, "{$sb->code} harvest soon", "Est. harvest in {$days} day" . ($days > 1 ? 's' : '') . '.', route('worker.harvest', [], false), $external);
                $sent++;
            }
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
