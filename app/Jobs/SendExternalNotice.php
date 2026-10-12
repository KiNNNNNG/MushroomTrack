<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ExternalNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Sends one notice to one person by email / SMS. */
class SendExternalNotice implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public int $userId,
        public string $title,
        public ?string $body = null,
        public ?string $url = null,
        public ?int $noticeId = null,
    ) {}

    /** Sends right away by default; goes through the queue when NOTIFY_QUEUE=true. */
    public static function fire(int $userId, string $title, ?string $body = null, ?string $url = null, ?int $noticeId = null): void
    {
        $job = new static($userId, $title, $body, $url, $noticeId);
        config('mushroomtrack.notify.queue') ? dispatch($job) : dispatch_sync($job);
    }

    public function handle(ExternalNotifier $notifier): void
    {
        $user = User::find($this->userId);
        if (! $user) return;

        try {
            $notifier->deliver($user, $this->title, $this->body, $this->url, $this->noticeId);
        } catch (Throwable $e) {
            Log::warning('MushFungAI external notice failed', ['user' => $this->userId, 'error' => $e->getMessage()]);
        }
    }
}
