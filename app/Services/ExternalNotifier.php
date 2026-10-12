<?php

namespace App\Services;

use App\Mail\NoticeMail;
use App\Models\NoticeDelivery;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Delivers a notice outside the app (email and/or SMS), following each person's own switches
 * on the profile page. A failure here is logged and never breaks the page the person is on.
 */
class ExternalNotifier
{
    public function __construct(private Sms $sms) {}

    public function deliver(User $user, string $title, ?string $body, ?string $url, ?int $noticeId = null): void
    {
        if ($user->status !== 'active') return;

        if ($user->notify_email && filled($user->email)) {
            $link = $url ? url($url) : null;
            $this->attempt($user, $noticeId, 'email', $this->mask($user->email), $title, function () use ($user, $title, $body, $link) {
                Mail::to($user->email)->send(new NoticeMail($title, (string) $body, $link, (string) $user->name));

                return true;
            });
        }

        if ($user->notify_sms) {
            $to = Sms::normalize($user->phone);
            if (! $to) {
                $this->record($user, $noticeId, 'sms', '-', $title, 'skipped', 'No valid Philippine mobile number on the profile.');
            } else {
                $this->attempt($user, $noticeId, 'sms', $this->mask($to), $title, fn () => $this->sms->send($to, Sms::compose($title, $body)));
            }
        }
    }

    private function attempt(User $user, ?int $noticeId, string $channel, string $to, string $title, callable $send): void
    {
        try {
            $sent = $send();
            $this->record($user, $noticeId, $channel, $to, $title, $sent ? 'sent' : 'skipped', $sent ? null : 'Channel is switched off.');
        } catch (Throwable $e) {
            Log::warning("MushFungAI {$channel} delivery failed", ['user' => $user->id, 'error' => $e->getMessage()]);
            $this->record($user, $noticeId, $channel, $to, $title, 'failed', Str::limit($e->getMessage(), 240, ''));
        }
    }

    private function record(User $user, ?int $noticeId, string $channel, string $to, string $title, string $status, ?string $error = null): void
    {
        NoticeDelivery::create([
            'user_id' => $user->id, 'notice_id' => $noticeId, 'channel' => $channel, 'recipient' => $to,
            'title' => Str::limit($title, 250, ''), 'status' => $status, 'error' => $error,
        ]);
    }

    private function mask(string $value): string
    {
        if (str_contains($value, '@')) {
            [$name, $domain] = explode('@', $value, 2);

            return substr($name, 0, 1).'***@'.$domain;
        }

        return substr($value, 0, 4).'***'.substr($value, -4);
    }
}
