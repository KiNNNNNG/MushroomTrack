<?php

namespace App\Support;
use Carbon\CarbonInterface;   // add this (replaces: use Illuminate\Support\Carbon;)
use Illuminate\Support\Carbon;

/** The week / month / year / all-time filter used by the dashboard, store and exports. */
class DateRange
{
    public const OPTIONS = ['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'];

    /** Start of the range, or null for "all time". Unknown values fall back to this month. */
   

public static function from(?string $range): ?CarbonInterface
{
    return match ($range) {
        'week'  => now()->startOfWeek(),
        'year'  => now()->startOfYear(),
        'all'   => null,
        default => now()->startOfMonth(),
    };
}

    public static function label(?string $range): string
    {
        return self::OPTIONS[$range] ?? self::OPTIONS['month'];
    }
}
