<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WidgetController extends Controller
{
    /**
     * Renderiza la tabla de posiciones como widget embebible (iFrame o Shadow DOM).
     */
    public function embedStandings(Request $request, Tournament $tournament): View
    {
        $theme = $request->query('theme', 'light'); // light, dark, emerald, slate
        $primaryColor = $request->query('color', '#057a55');
        $showTitle = filter_var($request->query('title', true), FILTER_VALIDATE_BOOLEAN);

        $standings = $tournament->standings()
            ->with('team')
            ->orderBy('points', 'desc')
            ->orderByRaw('(goals_for - goals_against) desc')
            ->orderBy('goals_for', 'desc')
            ->get();

        return view('v1.public.widgets.standings', compact('tournament', 'standings', 'theme', 'primaryColor', 'showTitle'));
    }

    /**
     * Renderiza el fixture / próximos partidos como widget embebible.
     */
    public function embedFixture(Request $request, Tournament $tournament): View
    {
        $theme = $request->query('theme', 'light');
        $primaryColor = $request->query('color', '#057a55');

        $matches = $tournament->matches()
            ->with(['homeTeam', 'awayTeam', 'venue'])
            ->orderBy('match_date')
            ->take(12)
            ->get();

        return view('v1.public.widgets.fixture', compact('tournament', 'matches', 'theme', 'primaryColor'));
    }

    /**
     * API JSON liviana para clubes que construyen su propio frontend.
     */
    public function apiStandings(Tournament $tournament): JsonResponse
    {
        $standings = $tournament->standings()
            ->with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->get()
            ->map(fn ($s, $i) => [
                'position' => $i + 1,
                'team_id' => $s->team_id,
                'team_name' => $s->team?->name,
                'played' => $s->played,
                'won' => $s->won,
                'drawn' => $s->drawn,
                'lost' => $s->lost,
                'goals_for' => $s->goals_for,
                'goals_against' => $s->goals_against,
                'goal_difference' => $s->goal_difference,
                'points' => $s->points,
            ]);

        return response()->json([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'sport_type' => $tournament->sport_type,
            ],
            'standings' => $standings,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
