<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('siget:send-reminders', function (\App\Services\v1\NotificationDispatcherService $dispatcher) {
    $upcomingMatches = \App\Models\v1\MatchGame::where('status', 'scheduled')
        ->whereBetween('match_date', [now(), now()->addHours(48)])
        ->with(['homeTeam.coach', 'awayTeam.coach'])
        ->get();

    $this->info('Escaneando partidos próximos para envío de recordatorios de nómina...');
    $count = 0;
    foreach ($upcomingMatches as $match) {
        if ($match->homeTeam) {
            $dispatcher->sendLineupDeadlineReminder($match, $match->homeTeam);
            $count++;
        }
        if ($match->awayTeam) {
            $dispatcher->sendLineupDeadlineReminder($match, $match->awayTeam);
            $count++;
        }
    }
    $this->info("✓ Enviados {$count} recordatorios omnicanal a directores técnicos (WhatsApp / WebPush).");
})->purpose('Envía recordatorios omnicanal a los DTs para el envío de nóminas antes del cierre');

