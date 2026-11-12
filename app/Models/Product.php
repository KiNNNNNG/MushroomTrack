<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $guarded = [];

    public function orders() { return $this->hasMany(Order::class); }

    public function scopeWithReservedStock(Builder $query): Builder
    {
        return $query->withSum([
            'orders as reserved_stock_kg' => fn ($orders) => $orders->whereIn('status', ['pending', 'confirmed']),
        ], 'quantity_kg');
    }

    /** Physical stock less quantities committed to pending and confirmed orders. */
    public function getAvailableStockKgAttribute(): float
    {
        $reserved = array_key_exists('reserved_stock_kg', $this->attributes)
            ? $this->attributes['reserved_stock_kg']
            : $this->orders()->whereIn('status', ['pending', 'confirmed'])->sum('quantity_kg');

        return max(0, round((float) $this->stock_kg - (float) $reserved, 2));
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (empty($value) || str_contains($value, 'photo-1549007994') || str_contains($value, 'photo-1535254973040')) {
            if (str_contains(strtolower($this->name ?? ''), 'grey')) {
                return asset('images/products/grey-oyster-mushrooms.jpg');
            }
            return asset('images/products/white-oyster-mushrooms.jpg');
        }

        if (str_contains($value, 'white-oyster-mushrooms')) {
            return asset('images/products/white-oyster-mushrooms.jpg');
        }

        if (str_contains($value, 'grey-oyster-mushrooms')) {
            return asset('images/products/grey-oyster-mushrooms.jpg');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
