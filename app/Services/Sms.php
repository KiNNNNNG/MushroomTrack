<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sends text messages to Philippine mobile numbers.
 * Drivers (SMS_DRIVER in .env): semaphore (real SMS), log (writes to storage/logs/laravel.log), null (off).
 */
class Sms
{
    /** "+63 917 123 4567", "0917-123-4567", "639171234567" or "9171234567" -> "09171234567". Null when it is not a PH mobile number. */
    public static function normalize(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '' || $d === null) return null;

        if (str_starts_with($d, '63') && strlen($d) === 12) $d = '0'.substr($d, 2);
        elseif (strlen($d) === 10 && $d[0] === '9') $d = '0'.$d;

        return preg_match('/^09\d{9}$/', $d) ? $d : null;
    }

    /**
     * Plain text only: characters such as the peso sign or long dashes force the phone to use
     * UCS-2 encoding, which cuts one SMS from 160 to 70 characters and costs more.
     */
    public static function compose(string $title, ?string $body): string
    {
        $text = 'MushFungAI: '.$title.($body ? '. '.$body : '');
        $text = str_replace(['₱', '—', '–', '·', '→', '…', '“', '”', '‘', '’'], ['PHP ', '-', '-', '-', '->', '...', '"', '"', "'", "'"], $text);

        return Str::limit(Str::ascii($text), 300, '...');
    }

    /**
     * @return bool true = handed to the provider, false = SMS is switched off
     *
     * @throws RuntimeException when the provider refuses or cannot be reached
     */
    public function send(string $to, string $message): bool
    {
        return match (config('mushroomtrack.sms.driver')) {
            'semaphore' => $this->semaphore($to, $message),
            'log'       => $this->log($to, $message),
            default     => false,
        };
    }

    private function log(string $to, string $message): bool
    {
        Log::info("[SMS to {$to}] {$message}");

        return true;
    }

    private function semaphore(string $to, string $message): bool
    {
        $key = config('mushroomtrack.sms.semaphore_key');
        if (! filled($key)) throw new RuntimeException('SEMAPHORE_API_KEY is not set.');

        $response = Http::asForm()->timeout(10)->post('https://api.semaphore.co/api/v4/messages', array_filter([
            'apikey'     => $key,
            'number'     => $to,
            'message'    => $message,
            'sendername' => config('mushroomtrack.sms.sender'),
        ], fn ($v) => filled($v)));

        if ($response->failed()) throw new RuntimeException('Semaphore HTTP '.$response->status());

        // Success is a list of messages. Errors (bad key, no credits...) come back as an object of field errors.
        $json = $response->json();
        if (! is_array($json) || ! isset($json[0])) {
            throw new RuntimeException('Semaphore refused the message: '.Str::limit((string) json_encode($json), 120, ''));
        }
        if (strtolower((string) ($json[0]['status'] ?? '')) === 'failed') throw new RuntimeException('Semaphore reported the message as failed.');

        return true;
    }
}
