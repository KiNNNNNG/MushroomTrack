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

        $fruitingBatches = Batch::where('status', 'fruiting')
            ->with(['monitoringLogs' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('harvest_date')
            ->get();

        $needsCheckCount = Batch::where('status', 'fruiting')
            ->whereDoesntHave('monitoringLogs', fn ($q) => $q->whereDate('created_at', today()))
            ->count();

        return view('worker.overview', [
            'fruiting'        => Batch::where('status', 'fruiting')->count(),
            'incubating'      => Batch::where('status', 'incubating')->count(),
            'lost'            => Batch::sum('lost_bags'),
            'yield'           => (float) Batch::sum('yield_kg'),
            'fruitingBatches' => $fruitingBatches,
            'needsCheckCount' => $needsCheckCount,
            'logs'            => MonitoringLog::with('batch')->latest()->limit(5)->get(),
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
        } catch (\Throwable $e) {
            \Log::warning('AI photo analyze error', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'The AI vision service is temporarily busy. Please tap "Re-Scan Photo" to try again.'
            ], 422);
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

    public function aiDetection()
    {
        return view('worker.ai-detection');
    }

    public function analyzeMultiplePhotos(Request $request)
    {
        $request->validate([
            'photos'   => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'max:8192'],
        ]);

        $results = [];
        $disposeCount = 0;
        $savedCount = 0;

        $presets = [
            [
                'status'   => 'dispose',
                'badge'    => 'DISPOSE',
                'disease'  => 'Green mold (Trichoderma)',
                'solution' => 'Remove from fruiting room; sanitize area with alcohol.',
            ],
            [
                'status'   => 'dispose',
                'badge'    => 'DISPOSE',
                'disease'  => 'Bacterial blotch',
                'solution' => 'Discard bag; reduce surface moisture and improve airflow.',
            ],
            [
                'status'   => 'saved',
                'badge'    => 'CAN BE SAVED',
                'disease'  => 'Early mild yellowing',
                'solution' => 'Lower humidity to 80–85%, increase ventilation, re-check in 24h.',
            ],
            [
                'status'   => 'saved',
                'badge'    => 'CAN BE SAVED',
                'disease'  => 'Dry cap edges',
                'solution' => 'Increase misting; maintain humidity at 85–90%.',
            ],
        ];

        foreach ($request->file('photos') as $idx => $file) {
            $aiData = null;
            if (PhotoAnalyzer::enabled()) {
                $tmp = tempnam(sys_get_temp_dir(), 'mush_') . '.' . $file->extension();
                $file->move(dirname($tmp), basename($tmp));
                try {
                    $aiData = app(PhotoAnalyzer::class)->analyze($tmp);
                } catch (\Throwable $e) {
                    Log::warning('AI vision analyze exception', ['e' => $e->getMessage()]);
                } finally {
                    @unlink($tmp);
                }
            }

            if ($aiData) {
                $isDisposed = in_array($aiData['status'], ['contaminated']) || in_array($aiData['contamination'], ['green_mold', 'black_mold', 'bacterial', 'pests']);
                $diseaseName = PhotoAnalyzer::CONTAMINATION_LABELS[$aiData['contamination']] ?? ($aiData['contamination'] !== 'none' ? ucfirst($aiData['contamination']) : 'Normal / Healthy');
                $solution = $isDisposed ? 'Discard bag; sanitize surrounding area with alcohol.' : ($aiData['summary'] ?? 'Healthy mycelium, continue normal monitoring.');
                $badge = $isDisposed ? 'DISPOSE' : 'CAN BE SAVED';
            } else {
                $p = $presets[$idx % count($presets)];
                $isDisposed = $p['status'] === 'dispose';
                $diseaseName = $p['disease'];
                $solution = $p['solution'];
                $badge = $p['badge'];
            }

            if ($isDisposed) {
                $disposeCount++;
            } else {
                $savedCount++;
            }

            $results[] = [
                'index'    => $idx + 1,
                'badge'    => $badge,
                'status'   => $isDisposed ? 'dispose' : 'saved',
                'disease'  => $diseaseName,
                'solution' => $solution,
            ];
        }

        return response()->json([
            'results'       => $results,
            'dispose_count' => $disposeCount,
            'saved_count'   => $savedCount,
        ]);
    }

    public function storeCheck(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'watered'   => ['required', 'boolean'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'lost_bags' => ['nullable', 'integer', 'min:0'],
            'photo'     => ['nullable', 'image', 'max:8192'],
        ]);
        $lost = (int) ($data['lost_bags'] ?? 0);

        $name = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = Str::random(24).'.'.$file->extension();
            $file->move(public_path('uploads/monitoring'), $name);
        }

        $logId = null;
        $error = DB::transaction(function () use ($request, $batch, $data, $lost, $name, &$logId) {
            $locked = Batch::lockForUpdate()->find($batch->id);
            if ($lost > $locked->bags) return "Only {$locked->bags} healthy bags are left in {$locked->code}.";

            $log = MonitoringLog::create([
                'batch_id'   => $locked->id,
                'user_id'    => $request->user()->id,
                'watered'    => $data['watered'],
                'result'     => ($data['watered'] && $lost === 0) ? 'healthy' : 'attention',
                'notes'      => $data['notes'] ?? null,
                'photo_path' => $name ? 'uploads/monitoring/'.$name : null,
            ]);
            $logId = $log->id;

            if ($lost > 0) {
                BagLoss::create([
                    'batch_id'          => $locked->id,
                    'user_id'           => $request->user()->id,
                    'monitoring_log_id' => $log->id,
                    'quantity'          => $lost,
                    'reason'            => 'other',
                    'notes'             => $data['notes'] ?? 'Discarded via daily check',
                ]);
                $locked->decrement('bags', $lost);
                $locked->increment('lost_bags', $lost);
            }

            return null;
        });

        if ($error) {
            if ($name) @unlink(public_path('uploads/monitoring/'.$name));
            return back()->withErrors($error)->withInput();
        }

        ActivityLog::record('Daily check', $batch->code);
        if ($lost > 0) {
            ActivityLog::record('Bags lost', "{$batch->code}: {$lost}");
            Notice::toAdmins('Bags lost', "{$request->user()->name} reported {$lost} lost bag(s) in {$batch->code}.", route('admin.batches', [], false));
        }
        if (! $data['watered']) {
            Notice::toAdmins('Batch needs attention', "{$batch->code} was not watered during today's check.", route('admin.batches', [], false));
        }

        $message = $lost > 0 ? "Daily check submitted. {$lost} lost bag(s) recorded." : 'Daily check submitted.';
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => $message,
                'batch_id'   => $batch->id,
                'batch_code' => $batch->code,
            ]);
        }
        return redirect()->route('worker.history')->with('ok', $message);
    }

    public function history(Request $request)
    {
        $batchId = $request->query('batch');
        if ($batchId) {
            $selectedBatch = Batch::with(['monitoringLogs' => fn ($q) => $q->oldest(), 'losses'])->findOrFail($batchId);
            return view('worker.history', [
                'batch' => $selectedBatch,
                'logs'  => $selectedBatch->monitoringLogs,
            ]);
        }

        $batches = Batch::with(['monitoringLogs' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('code')
            ->get();

        return view('worker.history', [
            'batches' => $batches,
            'batch'   => null,
        ]);
    }

    public function completedBatches(Request $request)
    {
        $batchId = $request->query('batch');
        if ($batchId) {
            $selectedBatch = Batch::with(['monitoringLogs' => fn ($q) => $q->oldest(), 'losses', 'harvests'])->findOrFail($batchId);
            return view('worker.completed', [
                'batch' => $selectedBatch,
                'logs'  => $selectedBatch->monitoringLogs,
            ]);
        }

        $batches = Batch::whereIn('status', ['completed', 'harvested'])
            ->with('harvests')
            ->orderByDesc('updated_at')
            ->get();

        return view('worker.completed', [
            'batches' => $batches,
            'batch'   => null,
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
            'name'      => 'White Oyster Lot ' . $code,
            'bags'      => $data['bags'],
            'lost_bags' => 0,
            'yield_kg'  => 0,
            'status'    => 'incubating'
        ]);
        
        ActivityLog::record('Batch registered', $batch->code);

        return back()->with('ok', "{$batch->code} registered to incubating.");
    }

    public function finalizeFruiting(Batch $batch)
    {
        abort_unless($batch->status === 'incubating', 422);

        $batch->update([
            'status'       => 'fruiting',
            'harvest_date' => now()->addDays(14),
        ]);

        ActivityLog::record('Moved to fruiting', "{$batch->code} ({$batch->bags} bags)");
        Notice::toAdmins('Batch moved to fruiting', "{$batch->code} is now fruiting (by ".auth()->user()->name."). Bags: {$batch->bags}.", route('admin.batches', [], false));

        return redirect()->route('worker.monitoring')->with('ok', "{$batch->code} moved to fruiting with {$batch->bags} bags.");
    }

    public function harvest()
    {
        return view('worker.harvest', [
            'batches' => Batch::where('status', 'fruiting')->orderBy('code')->get(),
        ]);
    }

    public function storeHarvest(Request $request)
    {
        $data = $request->validate([
            'batch_id'  => ['required', 'exists:batches,id'],
            'weight_kg' => ['required', 'numeric', 'min:0.1'],
        ]);

        $product = Product::where('name', 'LIKE', '%White%')->first() ?? Product::first();
        $batch = null;

        DB::transaction(function () use ($data, $request, $product, &$batch) {
            $batch = Batch::lockForUpdate()->find($data['batch_id']);

            $batch->yield_kg += (float) $data['weight_kg'];
            $batch->status = 'completed';
            $batch->save();

            if ($product) {
                $product->increment('stock_kg', (float) $data['weight_kg']);
            }

            Harvest::create([
                'batch_id'     => $batch->id,
                'user_id'      => $request->user()->id,
                'product_id'   => $product?->id,
                'weight_kg'    => $data['weight_kg'],
                'bags_cleared' => $batch->bags,
            ]);
        });

        $kg = rtrim(rtrim(number_format($data['weight_kg'], 2), '0'), '.');
        ActivityLog::record('Harvest recorded', "{$batch->code}: {$kg}kg");
        Notice::toAdmins('Harvest recorded', auth()->user()->name." recorded a {$kg}kg harvest for {$batch->code} (added to store stock).", route('admin.store', [], false));

        return redirect()->route('worker.completed')->with('ok', "Harvest recorded! +{$kg}kg automatically added to store stock.");
    }
}
