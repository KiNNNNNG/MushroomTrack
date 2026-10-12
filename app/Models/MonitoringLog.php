<?php

namespace App\Models;

use App\Services\PhotoAnalyzer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MonitoringLog extends Model
{
    protected $guarded = [];
    protected $casts = ['watered' => 'boolean'];

    public function batch() { return $this->belongsTo(Batch::class); }
    public function loss() { return $this->hasOne(BagLoss::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function getResultLabelAttribute(): string
    {
        return match ($this->result) {
            'healthy'      => 'Healthy',
            'contaminated' => 'Contaminated',
            default        => 'Needs attention',
        };
    }

    /** One short line of what the AI saw, e.g. "Green mold · Young fruiting · ~3 days to harvest". Null when there is nothing to say. */
    public function getAiLineAttribute(): ?string
    {
        if (! $this->ai_analyzed_at || $this->ai_error) return null;

        $bits = [];
        if ($this->ai_contamination && $this->ai_contamination !== 'none') $bits[] = PhotoAnalyzer::CONTAMINATION_LABELS[$this->ai_contamination] ?? $this->ai_contamination;
        if ($this->ai_stage && $this->ai_stage !== 'unknown') $bits[] = PhotoAnalyzer::STAGE_LABELS[$this->ai_stage] ?? $this->ai_stage;
        if ($this->ai_days_to_harvest !== null) $bits[] = $this->ai_days_to_harvest === 0 ? 'ready to pick' : '~'.$this->ai_days_to_harvest.' day'.($this->ai_days_to_harvest === 1 ? '' : 's').' to harvest';

        return $bits ? implode(' · ', $bits) : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) return null;
        return Str::startsWith($this->photo_path, 'http') ? $this->photo_path : asset($this->photo_path);
    }
}
