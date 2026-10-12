<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagLoss extends Model
{
    protected $table = 'bag_losses';
    protected $guarded = [];

    public function batch() { return $this->belongsTo(Batch::class); }
    public function user() { return $this->belongsTo(User::class); }

    public static function labelFor(string $reason): string
    {
        return config('mushroomtrack.loss_reasons.'.$reason)
            ?? ($reason === config('mushroomtrack.admin_adjustment_reason') ? 'Admin adjustment' : ucfirst($reason));
    }

    public function getReasonLabelAttribute(): string
    {
        return static::labelFor($this->reason);
    }
}
