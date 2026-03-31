<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cachear todos os capítulos da Bíblia no 1º dia de cada mês às 3h
Schedule::command('bible:warm-cache')->monthlyOn(1, '03:00');
