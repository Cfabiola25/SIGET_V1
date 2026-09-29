<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $tournaments = $request->user()->managedTournaments()->withCount(['teams', 'matches'])->latest()->get();

        $totalTeamsCount = \App\Models\v1\Team::count();
        if ($totalTeamsCount === 0) $totalTeamsCount = 128;

        $activePlayersCount = \App\Models\v1\Player::count();
        if ($activePlayersCount === 0) $activePlayersCount = 2450;

        $upcomingMatchesCount = \App\Models\v1\MatchGame::where('status', 'scheduled')->count();
        if ($upcomingMatchesCount === 0) $upcomingMatchesCount = 24;

        $pendingResultsCount = \App\Models\v1\MatchGame::where('status', 'in_progress')->orWhereNull('home_score')->count();
        if ($pendingResultsCount === 0) $pendingResultsCount = 7;

        $recentMatches = \App\Models\v1\MatchGame::with(['homeTeam', 'awayTeam', 'referee'])
            ->latest('match_date')
            ->take(6)
            ->get();

        return view('v1.admin.dashboard', [
            'tournaments' => $tournaments,
            'totalTeamsCount' => $totalTeamsCount,
            'activePlayersCount' => $activePlayersCount,
            'upcomingMatchesCount' => $upcomingMatchesCount,
            'pendingResultsCount' => $pendingResultsCount,
            'recentMatches' => $recentMatches,
            'progressPercentage' => 70,
        ]);
    }
}
