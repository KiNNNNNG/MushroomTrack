<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Batch extends Model
{
    protected $guarded = [];
    protected $casts = [
        'harvest_date' => 'date',
        'disposed_at'  => 'datetime',
        'scanned_bags' => 'array',
    ];

    public function losses() { return $this->hasMany(BagLoss::class); }
    public function harvests() { return $this->hasMany(Harvest::class); }
    public function monitoringLogs() { return $this->hasMany(MonitoringLog::class); }

    public function getHealthAttribute(): int
    {
        $total = $this->bags + $this->lost_bags;
        return $total ? (int) round($this->bags / $total * 100) : 100;
    }

    /**
     * Whether this batch has used up all its allowed harvest flushes.
     */
    public function getFlushesExhaustedAttribute(): bool
    {
        return $this->flush_count >= ($this->max_flushes ?? 5);
    }

    /**
     * Remaining flushes before the bags should be disposed.
     */
    public function getFlushesRemainingAttribute(): int
    {
        return max(0, ($this->max_flushes ?? 5) - $this->flush_count);
    }

    /**
     * Estimated dispose date: roughly 14 days per flush cycle from the last harvest,
     * multiplied by remaining flushes. Returns null if already disposed or flushes exhausted.
     */
    public function getEstimatedDisposeDateAttribute(): ?Carbon
    {
        if ($this->disposed_at || $this->flushes_exhausted) return null;
        $remaining = $this->flushes_remaining;
        if ($remaining === 0) return now();

        // Use last harvest date if available, otherwise the planned harvest date, then today
        $base = $this->harvests()->latest()->value('created_at')
            ?? $this->harvest_date
            ?? now();

        return Carbon::parse($base)->addDays($remaining * 14);
    }

    /**
     * Expected yield for the NEXT flush based on the average kg per flush so far.
     * Uses a modest decline factor since each flush typically yields a bit less.
     */
    public function getExpectedNextYieldKgAttribute(): float
    {
        if ($this->flush_count === 0) {
            // First harvest — estimate ~0.15 kg per bag (typical oyster mushroom first flush)
            return round($this->bags * 0.15, 2);
        }
        $avgPerFlush = $this->yield_kg / $this->flush_count;
        // Each successive flush typically ~80% of the previous
        $declineFactor = 0.80 ** $this->flush_count;
        return round(max(0, $avgPerFlush * $declineFactor * $this->bags / max(1, ($this->bags + $this->lost_bags))), 2);
    }
}
