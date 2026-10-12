<?php

namespace App\Models;

use App\Jobs\SendExternalNotice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Notice extends Model
{
    protected $guarded = [];
    protected $casts = ['read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    /**
     * Puts a notice on the person's bell and, when $external is true, also emails / texts them
     * (each person's own switches on the profile page decide which of those actually happen).
     */
    public static function send(int $userId, string $title, ?string $body = null, ?string $url = null, bool $external = false): ?self
    {
        $notice = null;
        if (! User::whereKey($userId)->where('notify_in_app', false)->exists()) {
            $notice = static::create(['user_id' => $userId, 'title' => $title, 'body' => $body, 'url' => $url]);
        }
        if ($external) SendExternalNotice::fire($userId, $title, $body, $url, $notice?->id);

        return $notice;
    }

    /** Notify every active admin. */
    public static function toAdmins(string $title, ?string $body = null, ?string $url = null, bool $external = false): void
    {
        User::where('role', 'admin')->where('status', 'active')->pluck('id')
            ->each(fn ($id) => static::send($id, $title, $body, $url, $external));
    }

    /** Notify every active worker. */
    public static function toWorkers(string $title, ?string $body = null, ?string $url = null, bool $external = false): void
    {
        User::where('role', 'worker')->where('status', 'active')->pluck('id')
            ->each(fn ($id) => static::send($id, $title, $body, $url, $external));
    }

    /** Reminder that is created (and texted / emailed) at most once per day for the same title + text. */
    public static function once(int $userId, string $title, string $body, ?string $url = null, bool $external = false): void
    {
        $exists = static::where('user_id', $userId)->where('title', $title)->where('body', $body)
            ->whereDate('created_at', today())->exists();
        if (! $exists) static::send($userId, $title, $body, $url);

        // Outside messages have their own once-a-day guard, so a person with the bell switched off is not texted on every page visit
        if ($external && Cache::add('notice-once:'.$userId.':'.md5($title.'|'.$body).':'.today()->toDateString(), 1, now()->endOfDay())) {
            SendExternalNotice::fire($userId, $title, $body, $url);
        }
    }
}
