<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (empty($value) || str_contains($value, 'photo-1549007994') || str_contains($value, 'photo-1535254973040')) {
            if (str_contains(strtolower($this->name ?? ''), 'grey')) {
                return asset('images/products/grey-oyster-mushrooms.jpg');
            }
            return asset('images/products/white-oyster-mushrooms.jpg');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
