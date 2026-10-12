<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime'];
}
