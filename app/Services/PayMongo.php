<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Legacy PayMongo reconciliation only. New checkout sessions cannot be created.
 * Docs: https://docs.paymongo.com/docs/payment-channels-hosted-checkout
 */
class PayMongo
{
    /** Our payment method key => PayMongo payment_method_types value */
    private const TYPES = ['gcash' => 'gcash', 'maya' => 'paymaya'];

    public static function configured(): bool
    {
        return filled(config('mushroomtrack.paymongo.secret_key'));
    }

    public static function isOnlineMethod(string $method): bool
    {
        return isset(self::TYPES[$method]);
    }

    private function version(): string
    {
        return config('mushroomtrack.paymongo.api_version') === 'v1' ? 'v1' : 'v2';
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl('https://api.paymongo.com')
            ->withBasicAuth((string) config('mushroomtrack.paymongo.secret_key'), '')
            ->acceptJson()->asJson()
            ->timeout((int) config('mushroomtrack.paymongo.timeout', 20));
    }

    /**
     * Asks PayMongo whether a checkout session has been paid (used when the customer comes back
     * from the payment page, so it also works on localhost where webhooks cannot reach us).
     *
     * @return string|null the PayMongo payment id (pay_...) when paid, otherwise null
     */
    public function paidPaymentId(string $sessionId): ?string
    {
        $first  = $this->version();
        $second = $first === 'v2' ? 'v1' : 'v2';

        $response = $this->http()->get("/{$first}/checkout_sessions/{$sessionId}");
        if ($response->failed()) $response = $this->http()->get("/{$second}/checkout_sessions/{$sessionId}");
        if ($response->failed()) throw new RuntimeException('Could not read the payment status (HTTP '.$response->status().').');

        return $this->paidIdFrom((array) $response->json('data', []));
    }

    /** Looks inside a checkout session resource for a payment that is paid. Returns its id, or null. */
    public function paidIdFrom(array $session): ?string
    {
        foreach ((array) ($session['attributes']['payments'] ?? []) as $p) {
            if (($p['attributes']['status'] ?? null) === 'paid') return $p['id'] ?? 'paid';
        }
        if (($session['attributes']['status'] ?? null) === 'paid') return (string) ($session['id'] ?? 'paid');

        return null;
    }

    /** Header looks like: t=1496734173,te=<test signature>,li=<live signature>. Signed text is "t.rawBody". */
    public function verifySignature(string $rawBody, ?string $header): bool
    {
        $secret = config('mushroomtrack.paymongo.webhook_secret');
        if (! filled($secret) || ! filled($header)) return false;

        $parts = [];
        foreach (explode(',', $header) as $piece) {
            [$k, $v] = array_pad(explode('=', $piece, 2), 2, '');
            $parts[trim($k)] = trim($v);
        }
        if (($parts['t'] ?? '') === '') return false;

        $expected = hash_hmac('sha256', $parts['t'].'.'.$rawBody, (string) $secret);
        foreach (['te', 'li'] as $k) {
            if (($parts[$k] ?? '') !== '' && hash_equals($expected, $parts[$k])) return true;
        }

        return false;
    }

    /**
     * Understands both webhook envelopes PayMongo uses.
     *
     * @return array{type:?string,resource:array}
     */
    public function parseWebhook(string $rawBody): array
    {
        $json = json_decode($rawBody, true);
        $json = is_array($json) ? $json : [];

        $type     = $json['data']['attributes']['type'] ?? $json['data']['type'] ?? $json['type'] ?? null;
        $resource = $json['data']['attributes']['data'] ?? $json['data']['data'] ?? [];

        return ['type' => is_string($type) ? $type : null, 'resource' => is_array($resource) ? $resource : []];
    }

}
