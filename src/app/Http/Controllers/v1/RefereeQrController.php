<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchLineup;
use App\Services\v1\PlayerEligibilityService;
use App\Services\v1\QrCarnetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeQrController extends Controller
{
    public function scanConsole(MatchGame $match): View
    {
        $match->load([
            'homeTeam.players.profile',
            'awayTeam.players.profile',
            'lineups.player.profile',
        ]);

        return view('v1.referees.scanner', compact('match'));
    }

    public function verifyScan(
        Request $request,
        MatchGame $match,
        QrCarnetService $qrService,
        PlayerEligibilityService $eligibilityService
    ): JsonResponse {
        $validated = $request->validate([
            'qr_payload' => ['required', 'string'],
        ]);

        $verified = $qrService->verify($validated['qr_payload']);
        if (! $verified) {
            return response()->json([
                'success' => false,
                'status' => 'invalid_qr',
                'message' => 'Código QR inválido o carnet no reconocido en el sistema SIGET.',
            ], 422);
        }

        $player = $verified['player'];

        // Verificar si el jugador pertenece a uno de los dos equipos del partido
        $isHome = $player->team_id === $match->home_team_id;
        $isAway = $player->team_id === $match->away_team_id;

        if (! $isHome && ! $isAway) {
            return response()->json([
                'success' => false,
                'status' => 'wrong_match',
                'message' => "El jugador {$player->name} pertenece a {$player->team->name}, que no disputa este encuentro.",
                'player' => [
                    'name' => $player->name,
                    'team' => $player->team->name,
                ],
            ], 422);
        }

        // Evaluar Filtro Tripartito
        $check = $eligibilityService->check($player, $match);

        // Buscar registro en la alineación oficial
        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('player_id', $player->id)
            ->first();

        $lineupStatus = match (true) {
            $lineup && $lineup->is_starter => 'Titular',
            $lineup && ! $lineup->is_starter => 'Suplente',
            default => 'No Convocado en Nómina',
        };

        if ($check['eligible'] && $lineup) {
            $lineup->markVerified();
        }

        return response()->json([
            'success' => $check['eligible'],
            'status' => $check['eligible'] ? 'authorized' : 'blocked',
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'jersey_number' => $player->jersey_number,
                'document' => $player->identification_document,
                'position' => $player->profile?->position_label ?? 'Jugador',
                'team' => $player->team->name,
                'lineup_status' => $lineupStatus,
                'blood_type' => $player->medicalRecord?->blood_type ?? 'N/D',
                'verified_by_qr' => $lineup?->fresh()->verified_by_qr ?? false,
            ],
            'reasons' => $check['reasons'],
            'message' => $check['eligible']
                ? "✅ Jugador HABILITADO en cancha: {$player->name} (#{$player->jersey_number})"
                : '⛔ Jugador INHABILITADO: '.implode(' ', $check['reasons']),
        ]);
    }
}
