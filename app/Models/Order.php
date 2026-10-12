<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = ['customer_hidden' => 'boolean'];

    public const FULFILLMENT_LABELS = [
        'pending'          => 'Preparing',
        'ready_for_pickup' => 'Ready for pickup',
        'out_for_delivery' => 'Out for delivery',
        'picked_up'        => 'Picked up',
        'delivered'        => 'Delivered',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function payment() { return $this->belongsTo(Payment::class); }

    public function getDisplayCodeAttribute(): string
    {
        return $this->code ?? 'ORD-'.str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getFulfillmentStatusLabelAttribute(): string
    {
        return self::FULFILLMENT_LABELS[$this->fulfillment_status] ?? ucfirst(str_replace('_', ' ', (string) $this->fulfillment_status));
    }

    /** True while an online (GCash / Maya) payment for this order has not arrived yet. */
    public function isAwaitingPayment(): bool
    {
        $p = $this->payment;

        return $p && $p->status === 'unpaid' && $p->isOnline();
    }

    public static function newCode(): string
    {
        do { $c = 'ORD-'.random_int(1000, 9999); } while (static::where('code', $c)->exists());
        return $c;
    }

    /** @param array<string,mixed> $extra payment_id, fulfillment, delivery_address, contact_phone ... */
    public static function place(User $user, Product $product, float $qty, array $extra = []): self
    {
        return static::create($extra + [
            'code' => static::newCode(), 'user_id' => $user->id, 'product_id' => $product->id,
            'quantity_kg' => $qty, 'total' => $qty * $product->price, 'status' => 'pending',
        ]);
    }
}
