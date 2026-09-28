<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;
use App\Services\v1\BracketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BracketController extends Controller
{
    public function index(Tournament $tournament, BracketService $bracketService): View
    {
        $tournament->load(['teams', 'standings.team']);
        $bracketTree = $bracketService->getBracketTree($tournament);

        $hasBrackets = $bracketTree['final'] !== null
            || $bracketTree['semifinals']->isNotEmpty()
            || $bracketTree['quarterfinals']->isNotEmpty();

        return view('v1.tournaments.brackets', compact('tournament', 'bracketTree', 'hasBrackets'));
    }

    public function generateBrackets(
        Request $request,
        Tournament $tournament,
        BracketService $bracketService
    ): RedirectResponse {
        $this->authorizeAdmin($tournament);

        $validated = $request->validate([
            'bracket_size' => ['required', 'integer', 'in:4,8'],
            'start_date' => ['nullable', 'date'],
            'days_between_stages' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $result = $bracketService->transitionFromStandings($tournament, $validated);

        return redirect()
            ->route('tournaments.brackets', $tournament)
            ->with('status', "¡Fase de eliminación directa activada! Se crearon {$result['total_matches_created']} partidos de Playoff para {$result['bracket_size']} clasificados.");
    }

    private function authorizeAdmin(Tournament $tournament): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $tournament->admin_id === $user->id)
            ),
            403
        );
    }
}
