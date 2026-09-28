<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Player;

class PlayerEligibilityService
{
    /**
     * Evalúa el Filtro Tripartito:
     * 1. Disciplinario (acumulación de tarjetas amarillas / rojas directas)
     * 2. Médico / Administrativo (firma de exoneración y aval médico)
     * 3. Financiero (bloqueo de nómina si el equipo está en mora)
     *
     * @return array{eligible: bool, reasons: array<string>}
     */
    public function check(Player $player, MatchGame $match): array
    {
        $reasons = [];

        // Filtro Financiero desactivado (pasarela de pagos excluida por el momento)

        // 2. Filtro Disciplinario
        if ($player->hasActiveSanctionInTournament($match->tournament_id)) {
            $activeSanction = $player->sanctions()
                ->where('tournament_id', $match->tournament_id)
                ->where('status', 'active')
                ->whereColumn('matches_served', '<', 'matches_suspended')
                ->first();

            $pending = $activeSanction ? $activeSanction->remainingMatches() : 1;
            $type = match ($activeSanction?->sanction_type) {
                'direct_red' => 'expulsión con tarjeta roja directa',
                'double_yellow' => 'doble tarjeta amarilla en el partido anterior',
                default => 'acumulación de tarjetas amarillas',
            };

            $reasons[] = "Sanción disciplinaria vigente por {$type} ({$pending} fecha(s) de suspensión pendiente).";
        }

        // 3. Filtro Médico y Administrativo
        $medical = $player->medicalRecord;
        if (! $medical || ! $medical->isClearedForMatch()) {
            if ($medical && ! $medical->waiver_signed) {
                $reasons[] = 'Ficha médica incompleta: pendiente firma digital de exoneración de responsabilidades.';
            } else {
                $reasons[] = 'Inhabilitación médica: el jugador no cuenta con el aval médico obligatorio.';
            }
        }

        return [
            'eligible' => count($reasons) === 0,
            'reasons' => $reasons,
        ];
    }
}
