<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\MonitoringLog;
use App\Models\Notice;
use App\Services\PhotoAnalyzer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Asks the vision model to review one daily-check photo, then:
 *  - fills the log's "result" (never makes a worker-reported problem look healthy),
 *  - nudges the batch's estimated harvest date when the model is confident,
 *  - alerts the admins when contamination is spotted.
 * If the AI is unavailable the check simply keeps the result the worker's answers produced.
 */
class AnalyzeMonitoringPhoto implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 90;

    public function __construct(public int $logId) {}

    /** Runs right away by default; goes through the queue when NOTIFY_QUEUE=true. */
    public static function run(int $logId): void
    {
        config('mushroomtrack.notify.queue') ? dispatch(new static($logId)) : dispatch_sync(new static($logId));
    }

    public function handle(PhotoAnalyzer $ai): void
    {
        $log = MonitoringLog::with('batch')->find($this->logId);
        if (! $log || ! $log->batch || $log->ai_analyzed_at || ! PhotoAnalyzer::enabled()) return;
        $batch = $log->batch;

        $last = MonitoringLog::where('batch_id', $batch->id)->where('id', '<>', $log->id)->whereNotNull('ai_stage')->latest()->first();

        try {
            $r = $ai->analyze(public_path((string) $log->photo_path), [
                'batch'           => $batch->name,
                'today'           => today()->toDateString(),
                'planned_harvest' => $batch->harvest_date ? $batch->harvest_date->toDateString() : null,
                'previous'        => $last
                    ? $last->created_at->toDateString().': '.(PhotoAnalyzer::STAGE_LABELS[$last->ai_stage] ?? $last->ai_stage)
                        .($last->ai_days_to_harvest !== null ? ", about {$last->ai_days_to_harvest} days to harvest" : '')
                    : null,
            ]);
        } catch (Throwable $e) {
            Log::warning('MushFungAI photo review skipped', ['log' => $log->id, 'error' => $e->getMessage()]);
            $message = $e instanceof ConnectionException ? 'AI service unreachable'
                : ($e instanceof RuntimeException ? $e->getMessage() : 'AI review failed');
            $log->update(['ai_error' => Str::limit($message, 190, ''), 'ai_analyzed_at' => now()]);

            return;
        }

        // The AI can only make the result worse, never better, than what the worker's own answers gave.
        $result = $log->result;
        if ($r['status'] === 'contaminated' && $r['confidence'] !== 'low') {
            $result = 'contaminated';
        } elseif (in_array($r['status'], ['contaminated', 'attention'], true) && $result === 'healthy') {
            $result = 'attention';
        }

        $log->update([
            'result'             => $result,
            'ai_status'          => $r['status'],
            'ai_contamination'   => $r['contamination'],
            'ai_confidence'      => $r['confidence'],
            'ai_stage'           => $r['stage'],
            'ai_days_to_harvest' => $r['days_to_harvest'],
            'ai_summary'         => $r['summary'] !== '' ? $r['summary'] : null,
            'ai_model'           => (string) config('mushroomtrack.ai.model'),
            'ai_error'           => null,
            'ai_analyzed_at'     => now(),
        ]);

        $this->moveHarvestDate($log, $r);

        if ($result === 'contaminated') {
            $what = strtolower(PhotoAnalyzer::CONTAMINATION_LABELS[$r['contamination']] ?? 'contamination');
            Notice::toAdmins(
                'Possible contamination',
                "AI spotted {$what} in {$batch->code} ({$r['confidence']} confidence). {$r['summary']}",
                route('admin.batches', [], false),
                true
            );
        }
    }

    private function moveHarvestDate(MonitoringLog $log, array $r): void
    {
        $batch = $log->batch;
        if (! config('mushroomtrack.ai.auto_harvest_date')) return;
        if ($batch->status !== 'fruiting' || $r['days_to_harvest'] === null) return;
        if ($r['confidence'] === 'low' || in_array($r['status'], ['contaminated', 'unclear'], true)) return;

        $new   = today()->addDays($r['days_to_harvest']);
        $old   = $batch->harvest_date;
        $shift = $old ? (int) abs($old->copy()->startOfDay()->diffInDays($new)) : PHP_INT_MAX;
        if ($shift < (int) config('mushroomtrack.ai.min_date_shift', 2)) return;

        $batch->update(['harvest_date' => $new->toDateString()]);
        ActivityLog::record('AI harvest estimate', "{$batch->code}: ".($old ? $old->toDateString() : 'not set').' -> '.$new->toDateString());
    }
}
