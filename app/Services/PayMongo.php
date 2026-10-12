<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Minimal PayMongo client: hosted Checkout Sessions for GCash and Maya.
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
     * Starts a hosted checkout for a payment and returns [session id, checkout url].
     *
     * @throws RuntimeException
     */
    public function createCheckout(Payment $payment): array
    {
        if (! self::configured()) throw new RuntimeException('PayMongo is not set up yet.');
        if (! self::isOnlineMethod($payment->method)) throw new RuntimeException('This payment method is not paid online.');

        $lines = [];
        foreach ($payment->orders()->with('product')->get() as $o) {
            $kg = rtrim(rtrim(number_format((float) $o->quantity_kg, 2), '0'), '.');
            // Quantity must be a whole number, so a line is "name (x kg)" with the line total as its amount.
            $lines[] = ['name' => Str::limit("{$o->product->name} ({$kg}kg)", 120, ''), 'amount' => $this->centavos($o->total), 'currency' => 'PHP', 'quantity' => 1];
        }
        if ((float) $payment->delivery_fee > 0) {
            $lines[] = ['name' => 'Delivery fee', 'amount' => $this->centavos($payment->delivery_fee), 'currency' => 'PHP', 'quantity' => 1];
        }

        $response = $this->http()->post("/{$this->version()}/checkout_sessions", ['data' => ['attributes' => [
            'line_items'           => $lines,
            'payment_method_types' => [self::TYPES[$payment->method]],
            'success_url'          => route('customer.pay.return', $payment),
            'cancel_url'           => route('customer.pay.cancelled', $payment),
            'reference_number'     => $payment->reference,
            'description'          => 'MushFungAI order '.$payment->reference,
            'metadata'             => ['payment_id' => (string) $payment->id],
        ]]]);

        if ($response->failed()) {
            Log::warning('PayMongo checkout failed', ['status' => $response->status(), 'body' => Str::limit($response->body(), 600)]);
            $detail = $response->json('errors.0.detail');
            throw new RuntimeException('PayMongo said: '.($detail ?: 'HTTP '.$response->status()));
        }

        $id  = $response->json('data.id');
        $url = $response->json('data.attributes.checkout_url');
        if (! $id || ! $url) throw new RuntimeException('PayMongo did not return a checkout link.');

        return [$id, $url];
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

    private function centavos(mixed $pesos): int
    {
        return (int) round(((float) $pesos) * 100);
    }
}
