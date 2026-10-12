<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeMonitoringPhoto;
use App\Models\ActivityLog;
use App\Models\Notice;
use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Harvest;
use App\Models\MonitoringLog;
use App\Models\Product;
use App\Services\PhotoAnalyzer;
use App\Services\Reminders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WorkerController extends Controller
{
    public function overview()
    {
        // Gentle once-a-day reminders for this worker (the 8am scheduled job also texts / emails them)
        Reminders::forWorker(auth()->user());

        return view('worker.overview', [
            'fruiting'   => Batch::where('status', 'fruiting')->count(),
            'incubating' => Batch::where('status', 'incubating')->count(),
            'lost'       => Batch::sum('lost_bags'),
            'yield'      => (float) Batch::sum('yield_kg'),
            'logs'       => MonitoringLog::with('batch')->latest()->limit(5)->get(),
        ]);
    }

    public function monitoring()
    {
        return view('worker.monitoring', ['batches' => Batch::where('status', 'fruiting')->orderBy('code')->get()]);
    }

    /** Target of the legacy batch-level QR code: opens that batch's daily-check form. */
    public function scan(Batch $batch)
    {
        return match ($batch->status) {
            'fruiting'   => redirect()->route('worker.monitoring', ['open' => $batch->code]),
            'incubating' => redirect()->route('worker.register')->withErrors("{$batch->code} is still incubating. Move it to fruiting before starting daily checks."),
            default      => redirect()->route('worker.monitoring')->withErrors("{$batch->code} is already harvested, so it has no daily check."),
        };
    }

    /** Target of per-bag QR codes. */
    public function bagMonitoring(Batch $batch, int $bag)
    {
        $totalBags = $batch->bags + $batch->lost_bags;
        if ($bag < 1 || $bag > max(1, $totalBags)) {
            return redirect()->route('worker.monitoring')->withErrors("Bag #{$bag} is not valid for {$batch->code}.");
        }

        if ($batch->status === 'incubating') {
            return view('worker.bag-incubating', [
                'batch' => $batch,
                'bagNum' => $bag,
                'totalBags' => $totalBags,
                'status' => $batch->scanned_bags[$bag] ?? null,
            ]);
        }

        $logs = MonitoringLog::with('user', 'loss')
            ->where('batch_id', $batch->id)
            ->latest()
            ->limit(20)
            ->get();

        $lastAiLog = $logs->firstWhere('ai_analyzed_at', '!=', null);

        return view('worker.bag-monitoring', [
            'batch'      => $batch,
            'bagNum'     => $bag,
            'totalBags'  => $totalBags,
            'logs'       => $logs,
            'lastAiLog'  => $lastAiLog,
        ]);
    }

    /** Live AI photo scan called from the daily-check dialog (returns JSON, does not save). */
    public function analyzePhoto(Request $request)
    {
        abort_unless(PhotoAnalyzer::enabled(), 503, 'AI review is not configured.');

        $request->validate(['photo' => ['required', 'image', 'max:8192']]);

        $file = $request->file('photo');
        $tmp  = tempnam(sys_get_temp_dir(), 'mush_') . '.' . $file->extension();
        $file->move(dirname($tmp), basename($tmp));

        try {
            $ai = app(PhotoAnalyzer::class)->analyze($tmp);
        } finally {
            @unlink($tmp);
        }

        return response()->json([
            'status'          => $ai['status'],
            'contamination'   => $ai['contamination'],
            'confidence'      => $ai['confidence'],
            'stage'           => $ai['stage'],
            'days_to_harvest' => $ai['days_to_harvest'],
            'summary'         => $ai['summary'],
            'stage_label'     => \App\Services\PhotoAnalyzer::STAGE_LABELS[$ai['stage']] ?? $ai['stage'],
            'contamination_label' => \App\Services\PhotoAnalyzer::CONTAMINATION_LABELS[$ai['contamination']] ?? $ai['contamination'],
        ]);
    }

    public function storeCheck(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'watered'     => ['required', 'boolean'],
            'notes'       => ['nullable', 'string', 'max:1000'],
            'photo'       => ['required', 'image', 'max:8192'],
            'lost_bags'   => ['nullable', 'integer', 'min:0'],
            'loss_reason' => ['nullable', Rule::requiredIf(fn () => (int) $request->input('lost_bags') > 0), Rule::in(array_keys(config('mushroomtrack.loss_reasons')))],
        ]);
        $lost = (int) ($data['lost_bags'] ?? 0);

        $file = $request->file('photo');
        $name = Str::random(24).'.'.$file->extension();
        $file->move(public_path('uploads/monitoring'), $name);

        $logId = null;
        $error = DB::transaction(function () use ($request, $batch, $data, $lost, $name, &$logId) {
            $locked = Batch::lockForUpdate()->find($batch->id);
            if ($lost > $locked->bags) return "Only {$locked->bags} healthy bags are left in {$locked->code}.";

            $log = MonitoringLog::create([
                'batch_id' => $locked->id, 'user_id' => $request->user()->id,
                'watered' => $data['watered'], 'result' => ($data['watered'] && $lost === 0) ? 'healthy' : 'attention',
                'notes' => $data['notes'] ?? null, 'photo_path' => 'uploads/monitoring/'.$name,
            ]);
            $logId = $log->id;

            if ($lost > 0) {
                BagLoss::create([
                    'batch_id' => $locked->id, 'user_id' => $request->user()->id, 'monitoring_log_id' => $log->id,
                    'quantity' => $lost, 'reason' => $data['loss_reason'], 'notes' => $data['notes'] ?? null,
                ]);
                $locked->decrement('bags', $lost);
                $locked->increment('lost_bags', $lost);
            }

            return null;
        });
        if ($error) {
            @unlink(public_path('uploads/monitoring/'.$name));
            return back()->withErrors($error)->withInput();
        }

        ActivityLog::record('Daily check', $batch->code);
        if ($lost > 0) {
            $reason = config('mushroomtrack.loss_reasons.'.$data['loss_reason']);
            ActivityLog::record('Bags lost', "{$batch->code}: {$lost} ({$data['loss_reason']})");
            Notice::toAdmins('Bags lost', "{$request->user()->name} reported {$lost} lost bag(s) in {$batch->code} — {$reason}.", route('admin.reports', [], false));
        }
        if (! $data['watered']) {
            Notice::toAdmins('Batch needs attention', "{$batch->code} was not watered during today's check.", route('admin.batches', [], false));
        }

        $message = $lost > 0 ? "Daily check submitted. {$lost} lost bag(s) recorded." : 'Daily check submitted.';

        // Second pair of eyes: the AI reviews the photo and fills in the result. A failure here never blocks the check.
        if ($logId && PhotoAnalyzer::enabled()) {
            try {
                AnalyzeMonitoringPhoto::run($logId);
                $ai = MonitoringLog::find($logId);
                if ($ai?->ai_summary) $message .= ' AI review: '.$ai->ai_summary;
            } catch (Throwable $e) {
                Log::warning('MushFungAI photo review crashed', ['log' => $logId, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('worker.history')->with('ok', $message);
    }

    public function history(Request $request)
    {
        return view('worker.history', [
            'batches' => Batch::orderBy('code')->get(),
            'logs'    => MonitoringLog::with('batch', 'user', 'loss')
                ->when($request->query('batch'), fn ($q, $b) => $q->where('batch_id', $b))->latest()->get(),
        ]);
    }

    public function register()
    {
        return view('worker.register', ['batches' => Batch::where('status', 'incubating')->orderBy('code')->get()]);
    }

    public function storeBatch(Request $request)
    {
        $data = $request->validate([
            'bags' => ['required', 'integer', 'min:1'],
        ]);
        
        $latest = Batch::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $code = 'BATCH-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $batch = Batch::create([
            'code'      => $code,
            'name'      => 'Incubating ' . $code,
            'bags'      => $data['bags'],
            'lost_bags' => 0,
            'yield_kg'  => 0,
            'status'    => 'incubating'
        ]);
        
        ActivityLog::record('Batch registered', $batch->code);

        return back()->with('ok', "{$batch->code} registered to incubating.")->with('new_batch_id', $batch->id);
    }

    public function scanIncubatingBag(Request $request, Batch $batch, int $bag)
    {
        abort_unless($batch->status === 'incubating', 422);
        $request->validate(['status' => 'required|in:good,reject']);

        $scanned = $batch->scanned_bags ?? [];
        $scanned[$bag] = $request->input('status');
        $batch->update(['scanned_bags' => $scanned]);

        return back()->with('ok', "Bag #{$bag} marked as " . strtoupper($request->input('status')) . ".");
    }

    public function finalizeFruiting(Batch $batch)
    {
        abort_unless($batch->status === 'incubating', 422);
        
        $scanned = $batch->scanned_bags ?? [];
        $good = 0;
        $reject = 0;
        foreach ($scanned as $status) {
            if ($status === 'good') $good++;
            elseif ($status === 'reject') $reject++;
        }
        
        // Unscanned bags are assumed rejected (or we can just leave them as lost)
        $totalBags = $batch->bags + $batch->lost_bags;
        $unscanned = max(0, $totalBags - count($scanned));
        $lost = $reject + $unscanned;

        $batch->update([
            'status' => 'fruiting', 
            'harvest_date' => $batch->harvest_date ?? now()->addDays(14),
            'bags' => $good,
            'lost_bags' => $lost,
        ]);

        ActivityLog::record('Moved to fruiting', "{$batch->code} ({$good} good, {$lost} lost)");
        Notice::toAdmins('Batch moved to fruiting', "{$batch->code} is now fruiting (by ".auth()->user()->name."). Good: {$good}, Rejected/Lost: {$lost}.", route('admin.batches', [], false));

        return redirect()->route('worker.register')->with('ok', "{$batch->code} moved to fruiting with {$good} bags.");
    }

    /** Printable QR labels — one label per BAG within each batch. ?batch=ID prints one batch; otherwise all active. */
    public function labels(Request $request)
    {
        $batches = Batch::query()
            ->when($request->query('batch'), fn ($q, $id) => $q->whereKey($id), fn ($q) => $q->where('status', '!=', 'harvested'))
            ->orderBy('code')->get();

        $labels = [];
        foreach ($batches as $b) {
            $totalBags = $b->bags + $b->lost_bags; // original bag count
            for ($n = 1; $n <= max(1, $totalBags); $n++) {
                $url = route('worker.bag', ['batch' => $b->code, 'bag' => $n]);
                $labels[] = [
                    'batch'  => $b,
                    'bagNum' => $n,
                    'total'  => $totalBags,
                    'svg'    => \App\Support\Qr::svg($url, 180),
                ];
            }
        }

        return view('worker.labels', compact('labels'));
    }

    public function harvest()
    {
        return view('worker.harvest', [
            'batches' => Batch::where('status', 'fruiting')->orderBy('code')->get(),
            'products' => Product::orderBy('name')->get(),
            'recent'  => Harvest::with('batch', 'user', 'product')->latest()->limit(10)->get(),
        ]);
    }

    public function storeHarvest(Request $request)
    {
        $data = $request->validate([
            'batch_id'     => ['required', 'exists:batches,id'],
            'product_id'   => ['nullable', 'exists:products,id'],   // empty = do not add to the store
            'weight_kg'    => ['required', 'numeric', 'min:0.1'],
            'bags_cleared' => ['required', 'integer', 'min:0'],
        ]);

        $addedTo = null;
        $error = DB::transaction(function () use ($data, $request, &$addedTo) {
            $batch = Batch::lockForUpdate()->find($data['batch_id']);
            if ($data['bags_cleared'] > $batch->bags) return "Only {$batch->bags} bags left in {$batch->code}.";

            $batch->yield_kg += $data['weight_kg'];
            $batch->bags -= $data['bags_cleared'];
            $batch->flush_count = ($batch->flush_count ?? 0) + 1;
            // Mark disposed when all bags are gone OR max flushes reached
            if ($batch->bags === 0 || $batch->flush_count >= ($batch->max_flushes ?? 5)) {
                $batch->status = ($batch->bags === 0) ? 'harvested' : $batch->status;
                if ($batch->flush_count >= ($batch->max_flushes ?? 5) && ! $batch->disposed_at) {
                    $batch->disposed_at = now();
                }
            }
            $batch->save();

            if (! empty($data['product_id'])) {
                $addedTo = Product::lockForUpdate()->find($data['product_id']);
                $addedTo->increment('stock_kg', $data['weight_kg']);
            }

            Harvest::create($data + ['user_id' => $request->user()->id]);
            return null;
        });
        if ($error) return back()->withErrors($error)->withInput();

        $kg = rtrim(rtrim(number_format($data['weight_kg'], 2), '0'), '.');
        ActivityLog::record('Harvest recorded', $addedTo ? "{$kg}kg → {$addedTo->name}" : "{$kg}kg (not added to store)");
        Notice::toAdmins('Harvest recorded', auth()->user()->name." recorded a {$kg}kg harvest".($addedTo ? " (added to {$addedTo->name} stock)." : '.'), route('admin.reports', [], false));

        return back()->with('ok', $addedTo ? "Harvest recorded. +{$kg}kg added to {$addedTo->name} stock." : 'Harvest recorded (not added to the store).');
    }
}
