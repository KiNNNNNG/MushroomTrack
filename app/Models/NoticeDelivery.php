<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per SMS / email we tried to send, so a missing message can be explained. */
class NoticeDelivery extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function notice() { return $this->belongsTo(Notice::class); }
}
