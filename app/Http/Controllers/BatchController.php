<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Notice;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'code'         => ['nullable', 'string', 'max:30', 'unique:batches,code,'.$id],
            'name'         => ['required', 'string', 'max:120'],
            'bags'         => ['required', 'integer', 'min:0'],
            'lost_bags'    => ['required', 'integer', 'min:0'],
            'yield_kg'     => ['nullable', 'numeric', 'min:0'],
            'status'       => ['required', 'in:incubating,fruiting,harvested'],
            'harvest_date' => ['nullable', 'date'],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['yield_kg'] = $data['yield_kg'] ?? 0;
        $data['code'] = $data['code'] ?: 'BATCH-'.str_pad((Batch::max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        $batch = Batch::create($data);
        ActivityLog::record('Batch created', $batch->code.' — '.$batch->name);
        Notice::toWorkers('New batch', "{$batch->code} — {$batch->name} was added.",
            route($batch->status === 'incubating' ? 'worker.register' : 'worker.monitoring', [], false));

        return back()->with('ok', 'Batch added.');
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $request->validate($this->rules($batch->id));
        $data['yield_kg'] = $data['yield_kg'] ?? 0;
        $data['code'] = $data['code'] ?: $batch->code;

        $old = $batch->status;
        $oldLost = $batch->lost_bags;
        $batch->update($data);
        // Keep the loss records in step when an admin raises the lost-bags number by hand
        if ($batch->lost_bags > $oldLost) {
            BagLoss::create([
                'batch_id' => $batch->id, 'user_id' => auth()->id(), 'quantity' => $batch->lost_bags - $oldLost,
                'reason' => config('mushroomtrack.admin_adjustment_reason'), 'notes' => 'Edited by admin',
            ]);
        }
        ActivityLog::record('Batch updated', $batch->code);
        if ($old !== $batch->status) {
            Notice::toWorkers('Batch status changed', "{$batch->code} is now {$batch->status}.", route('worker.overview', [], false));
        }

        return back()->with('ok', 'Batch updated.');
    }

    public function destroy(Batch $batch)
    {
        $batch->delete();
        ActivityLog::record('Batch deleted', $batch->code);
        Notice::toWorkers('Batch removed', "{$batch->code} was removed by an admin.", route('worker.overview', [], false));

        return back()->with('ok', 'Batch deleted.');
    }
}
