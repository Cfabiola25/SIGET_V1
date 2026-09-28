<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;

class PublicController extends Controller
{
    public function home()
    {
        return view('v1.public.home', [
            'tournaments' => Tournament::withCount(['teams', 'matches'])->whereIn('status', ['pending', 'active'])->latest()->take(3)->get(),
        ]);
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
