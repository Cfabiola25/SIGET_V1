<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;

class PublicController extends Controller
{
    public function home()
    {
        $tournament = Tournament::with([
            'standings.team',
            'teams.captain',
            'teams.players.profile',
            'matches.homeTeam.players.profile',
            'matches.awayTeam.players.profile',
            'matches.venue',
            'matches.referee',
            'matches.mvpPlayer',
            'matches.events.player',
        ])
            ->whereIn('status', ['active', 'pending'])
            ->latest()
            ->first();

        $standings = collect();
        $featuredMatches = collect();
        $teams = collect();
        $chronicles = collect();

        if ($tournament) {
            $standings = $tournament->standings
                ->sortByDesc(fn ($s) => [$s->points, ($s->goals_for - $s->goals_against), $s->goals_for])
                ->values();

            $featuredMatches = $tournament->matches
                ->sortBy('match_date')
                ->values();

            $teams = $tournament->teams;

            $chronicles = $tournament->matches
                ->whereNotNull('chronicle_title')
                ->sortByDesc('match_date')
                ->values();
        }

        $allTournaments = Tournament::withCount(['teams', 'matches'])->latest()->take(6)->get();

        return view('v1.public.home', compact(
            'tournament',
            'standings',
            'featuredMatches',
            'teams',
            'chronicles',
            'allTournaments'
        ));
    }

    public function tournament()
    {
        $tournament = Tournament::with(['teams', 'matches.homeTeam', 'matches.awayTeam', 'standings.team'])
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (! $tournament) {
            return redirect()->route('tournaments.index')->with('status', 'No hay torneos activos disponibles en este momento.');
        }

        return view('v1.public.tournament', compact('tournament'));
    }
}
