<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:generate-daily')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('reports:generate-monthly')->monthlyOn(1, '02:00')->withoutOverlapping();
