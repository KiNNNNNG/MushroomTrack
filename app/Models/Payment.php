<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Money for one checkout. It can cover several orders (a cart) plus a delivery fee.
 * status: unpaid | paid | refund_due | refunded
 */
class Payment extends Model
{
    protected $guarded = [];
    protected $casts = ['paid_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function orders() { return $this->hasMany(Order::class); }

    public function isOnline(): bool
    {
        return \App\Services\PayMongo::isOnlineMethod($this->method);
    }

    public function getMethodLabelAttribute(): string
    {
        return config('mushroomtrack.payment_methods.'.$this->method) ?? ucfirst($this->method);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid'       => 'Paid',
            'refund_due' => 'Refund due',
            'refunded'   => 'Refunded',
            default      => 'Unpaid',
        };
    }

    public static function newReference(): string
    {
        do { $r = 'PAY-'.random_int(100000, 999999); } while (static::where('reference', $r)->exists());

        return $r;
    }

    /** Delivery fee for a checkout: 0 for pickup, or when the order is big enough to qualify for free delivery. */
    public static function deliveryFeeFor(string $fulfillment, float $subtotal): float
    {
        if ($fulfillment !== 'delivery') return 0.0;

        $freeOver = (float) config('mushroomtrack.delivery.free_over');
        if ($freeOver > 0 && $subtotal >= $freeOver) return 0.0;

        return round((float) config('mushroomtrack.delivery.fee'), 2);
    }

    /**
     * Opens (or re-opens) the PayMongo hosted checkout and returns its URL.
     *
     * @throws \RuntimeException when PayMongo is not set up or refuses the request
     */
    public function startCheckout(): string
    {
        [$id, $url] = app(\App\Services\PayMongo::class)->createCheckout($this);
        $this->update(['paymongo_session_id' => $id, 'checkout_url' => $url]);

        return $url;
    }

    /** Called when an admin cancels an order whose money already arrived: flags the refund. Returns true when it did (and already told the customer). */
    public function orderCancelled(Order $order): bool
    {
        if (! in_array($this->status, ['paid', 'refund_due'], true)) return false;

        $everything = ! $this->orders()->where('status', '<>', 'cancelled')->exists();
        $due = (float) $order->total + ($everything ? (float) $this->delivery_fee : 0);
        $this->update(['status' => 'refund_due']);

        $amount = rtrim(rtrim(number_format($due, 2), '0'), '.');
        $who = optional($this->user)->name ?? 'A customer';
        Notice::toAdmins('Refund needed', "{$order->display_code} was cancelled after payment. Refund ₱{$amount} to {$who} ({$this->method_label}, {$this->reference}).", route('admin.store', ['tab' => 'history'], false), true);
        Notice::send($this->user_id, 'Order cancelled', "{$order->display_code} was cancelled. Your ₱{$amount} refund is being processed.", route('customer.history', [], false), true);

        return true;
    }

    /**
     * Marks the payment as paid. Safe to call twice (webhook + customer coming back): only the
     * first call does anything. Paid orders that were still pending become confirmed.
     */
    public function settle(?string $providerPaymentId = null): bool
    {
        $changed = DB::transaction(function () use ($providerPaymentId) {
            $p = static::lockForUpdate()->find($this->id);
            if (! $p || $p->status !== 'unpaid') return false;

            $p->update(['status' => 'paid', 'paid_at' => now(), 'paymongo_payment_id' => $providerPaymentId ?? $p->paymongo_payment_id]);
            $p->orders()->where('status', 'pending')->update(['status' => 'confirmed']);

            return true;
        });
        if (! $changed) return false;

        $this->refresh();
        $orders = $this->orders()->get();
        $codes  = $orders->map->display_code->implode(', ');
        $amount = rtrim(rtrim(number_format((float) $this->amount, 2), '0'), '.');
        $who    = optional($this->user)->name ?? 'A customer';
        $live   = $orders->whereIn('status', ['confirmed', 'completed'])->isNotEmpty();

        ActivityLog::record('Payment received', "{$this->reference}: PHP {$amount} via {$this->method_label}");

        if ($live && $this->method === 'cash') {
            Notice::send($this->user_id, 'Payment recorded', "Your cash payment of ₱{$amount} for {$codes} is recorded. Thank you!", route('customer.history', [], false), true);
        } elseif ($live) {
            Notice::send($this->user_id, 'Payment received', "We received your {$this->method_label} payment of ₱{$amount} for {$codes}. Your order is confirmed.", route('customer.orders', [], false), true);
            Notice::toAdmins('Order paid', "{$who} paid ₱{$amount} via {$this->method_label} ({$codes}). Ready to prepare.", route('admin.store', ['tab' => 'orders'], false), true);
        } else {
            // Paid after every order was cancelled (for example an old payment link): the money has to go back.
            $this->update(['status' => 'refund_due']);
            Notice::toAdmins('Refund needed', "{$who} paid ₱{$amount} ({$this->reference}) but the order was already cancelled. Please refund it in PayMongo.", route('admin.store', ['tab' => 'history'], false), true);
        }

        return true;
    }
}
