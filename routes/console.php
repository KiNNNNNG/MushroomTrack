<?php

use Illuminate\Foundation\Inspiring;
use App\Models\User;
use App\Services\Reminders;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Morning nudge by bell, email and (opt-in) SMS. Needs `php artisan schedule:work` (or a Windows Task Scheduler job running schedule:run every minute).
Artisan::command('mushroomtrack:remind', function () {
    $n = 0;
    User::where('role', 'worker')->where('status', 'active')->get()
        ->each(function (User $w) use (&$n) { $n += Reminders::forWorker($w, true); });
    $this->info("Reminders created: {$n}");
})->purpose('Remind workers about daily checks and harvest-ready batches');

Schedule::command('mushroomtrack:remind')->dailyAt('08:00')->withoutOverlapping();
