<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Limpa backups antigos (conforme regras em config/backup.php) todos os dias às 03:00 da manhã
Schedule::command('backup:clean')->daily()->at('03:00');

// Executa o novo backup a cada minuto (temporário para testes)
//Schedule::command('backup:run')->everyMinute();

// Executa o novo backup todos os dias às 03:30 da manhã
Schedule::command('backup:run')->daily()->at('03:30');
