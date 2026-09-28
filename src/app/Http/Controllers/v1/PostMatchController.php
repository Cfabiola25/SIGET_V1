<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchSignature;
use App\Services\v1\PostMatchClosureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostMatchController extends Controller
{
    public function showClosure(MatchGame $match): View
    {
        $this->authorizeRefereeOrAdmin($match);

        $match->load([
            'homeTeam.players.profile',
            'awayTeam.players.profile',
            'events.player',
            'events.subInPlayer',
            'events.team',
            'signatures',
            'lineups.player.profile',
            'tournament.rules',
            'venue',
            'referee',
        ]);

        return view('v1.matches.closure', compact('match'));
    }

    public function saveSignaturesAndClose(
        Request $request,
        MatchGame $match,
        PostMatchClosureService $closureService
    ): RedirectResponse {
        $this->authorizeRefereeOrAdmin($match);

        if ($match->isLocked()) {
            return redirect()
                ->route('matches.report', $match)
                ->with('status', 'El partido ya ha sido cerrado y cuenta con acta oficial inmutable.');
        }

        $validated = $request->validate([
            'referee_name' => ['required', 'string', 'max:120'],
            'referee_signature' => ['required', 'string'],
            'home_coach_name' => ['required', 'string', 'max:120'],
            'home_coach_signature' => ['required', 'string'],
            'away_coach_name' => ['required', 'string', 'max:120'],
            'away_coach_signature' => ['required', 'string'],
            'match_sheet_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $now = now();
        $ip = $request->ip();

        // 1. Almacenar firma Árbitro Principal
        MatchSignature::updateOrCreate(
            ['match_id' => $match->id, 'signer_role' => 'referee'],
            [
                'signer_name' => $validated['referee_name'],
                'signature_data' => $validated['referee_signature'],
                'signed_at' => $now,
                'ip_address' => $ip,
            ]
        );

        // 2. Almacenar firma DT / Delegado Local
        MatchSignature::updateOrCreate(
            ['match_id' => $match->id, 'signer_role' => 'home_coach'],
            [
                'signer_name' => $validated['home_coach_name'],
                'signature_data' => $validated['home_coach_signature'],
                'signed_at' => $now,
                'ip_address' => $ip,
            ]
        );

        // 3. Almacenar firma DT / Delegado Visitante
        MatchSignature::updateOrCreate(
            ['match_id' => $match->id, 'signer_role' => 'away_coach'],
            [
                'signer_name' => $validated['away_coach_name'],
                'signature_data' => $validated['away_coach_signature'],
                'signed_at' => $now,
                'ip_address' => $ip,
            ]
        );

        // 4. Ejecutar cierre oficial atómico
        $result = $closureService->closeMatch($match, $validated['match_sheet_notes'] ?? null);

        return redirect()
            ->route('matches.report', $match)
            ->with('status', $result['message']);
    }

    public function officialReport(MatchGame $match): View
    {
        $match->load([
            'homeTeam.players.profile',
            'awayTeam.players.profile',
            'events.player',
            'events.subInPlayer',
            'events.team',
            'signatures',
            'lineups.player.profile',
            'tournament.rules',
            'venue',
            'referee',
        ]);

        $verificationHash = hash('sha256', "SIGET-ACTA-{$match->id}-{$match->home_score}:{$match->away_score}-{$match->locked_at}");

        return view('v1.matches.official-report', compact('match', 'verificationHash'));
    }

    private function authorizeRefereeOrAdmin(MatchGame $match): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $match->tournament->admin_id === $user->id)
            ),
            403
        );
    }
}
