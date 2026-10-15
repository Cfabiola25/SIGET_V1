<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\Tournament;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        $allTournaments = Tournament::withCount(['teams', 'matches'])
            ->with(['admin', 'rules'])
            ->latest()
            ->get();

        $tournamentId = $request->query('tournament_id');

        $relations = [
            'standings.team',
            'teams.captain',
            'teams.players.profile',
            'matches.homeTeam.players.profile',
            'matches.awayTeam.players.profile',
            'matches.venue',
            'matches.referee',
            'matches.mvpPlayer',
            'matches.events.player',
            'rules',
        ];

        $tournamentId = $request->query('tournament_id');

        if ($tournamentId) {
            $tournament = Tournament::with($relations)->find($tournamentId);
        } else {
            // Priorizar el torneo configurado como Principal / Destacado por el Super Admin
            $tournament = Tournament::with($relations)->where('is_featured', true)->first()
                ?? Tournament::with($relations)->whereIn('status', ['active', 'pending'])->latest()->first()
                ?? Tournament::with($relations)->latest()->first();
        }

        $standings = collect();
        $featuredMatches = collect();
        $recentMatches = collect();
        $upcomingMatches = collect();
        $teams = collect();
        $chronicles = collect();
        $topScorers = collect();
        $totalGoals = 0;
        $totalMatchesPlayed = 0;

        if ($tournament) {
            $standings = $tournament->standings
                ->sortByDesc(fn ($s) => [
                    $s->points,
                    ($s->goals_for - $s->goals_against),
                    $s->goals_for,
                    $s->wins,
                ])
                ->values();

            $allTournamentMatches = $tournament->matches;

            $featuredMatches = $allTournamentMatches
                ->sortBy(function ($m) {
                    if ($m->status === 'live' || $m->is_timer_running) {
                        return 0;
                    }
                    if ($m->status === 'scheduled') {
                        return 1;
                    }

                    return 2;
                })
                ->values();

            $upcomingMatches = $allTournamentMatches
                ->where('status', 'scheduled')
                ->sortBy('match_date')
                ->values();

            $recentMatches = $allTournamentMatches
                ->where('status', 'played')
                ->sortByDesc('match_date')
                ->values();

            $teams = $tournament->teams;

            // Crónicas deportivas generadas autónomamente por la IA Match Reporter al cerrar partidos
            $chronicles = $allTournamentMatches
                ->whereNotNull('chronicle_title')
                ->whereNotNull('chronicle_body')
                ->sortByDesc(fn ($m) => $m->chronicle_generated_at ?? $m->match_date)
                ->values();

            // Si el torneo actual aún no tiene crónicas concluidas, cargar crónicas recientes globales
            if ($chronicles->isEmpty()) {
                $chronicles = MatchGame::whereNotNull('chronicle_title')
                    ->whereNotNull('chronicle_body')
                    ->with(['homeTeam', 'awayTeam', 'tournament'])
                    ->latest('chronicle_generated_at')
                    ->take(3)
                    ->get();
            }

            $totalMatchesPlayed = $allTournamentMatches->where('status', 'played')->count();
            $totalGoals = $allTournamentMatches->where('status', 'played')->sum(fn ($m) => ($m->home_score ?? 0) + ($m->away_score ?? 0));

            $topScorers = MatchEvent::where('event_type', 'goal')
                ->whereHas('match', fn ($q) => $q->where('tournament_id', $tournament->id))
                ->with(['player.team', 'player.profile'])
                ->selectRaw('player_id, count(*) as goals')
                ->groupBy('player_id')
                ->orderByDesc('goals')
                ->take(5)
                ->get();
        }

        return view('v1.public.home', compact(
            'tournament',
            'standings',
            'featuredMatches',
            'recentMatches',
            'upcomingMatches',
            'teams',
            'chronicles',
            'allTournaments',
            'topScorers',
            'totalGoals',
            'totalMatchesPlayed'
        ));
    }

    public function tournament()
    {
        $tournament = Tournament::with(['teams', 'matches.homeTeam', 'matches.awayTeam', 'standings.team'])
            ->where('is_featured', true)
            ->first()
            ?? Tournament::with(['teams', 'matches.homeTeam', 'matches.awayTeam', 'standings.team'])
                ->whereIn('status', ['pending', 'active'])
                ->latest()
                ->first();

        if (! $tournament) {
            return redirect()->route('tournaments.index')->with('status', 'No hay torneos activos disponibles en este momento.');
        }

        return view('v1.public.tournament', compact('tournament'));
    }

    public function privacy()
    {
        return view('v1.public.privacy');
    }
}
