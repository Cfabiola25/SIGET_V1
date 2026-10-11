<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tournaments = $user->managedTournaments()->withCount(['teams', 'matches'])->latest()->get();
        $tournamentIds = $tournaments->pluck('id');

        $totalTeamsCount = Team::whereIn('tournament_id', $tournamentIds)->count();
        $activePlayersCount = Player::whereHas('team', fn ($q) => $q->whereIn('tournament_id', $tournamentIds))->count();
        $upcomingMatchesCount = MatchGame::whereIn('tournament_id', $tournamentIds)->where('status', 'scheduled')->count();
        $pendingResultsCount = MatchGame::whereIn('tournament_id', $tournamentIds)
            ->where(function ($q): void {
                $q->where('status', 'in_progress')->orWhereNull('home_score');
            })->count();

        $recentMatches = MatchGame::whereIn('tournament_id', $tournamentIds)
            ->with(['homeTeam', 'awayTeam', 'referee'])
            ->latest('match_date')
            ->take(6)
            ->get();

        $completedMatchesCount = MatchGame::whereIn('tournament_id', $tournamentIds)->where('status', 'played')->count();
        $allMatchesCount = MatchGame::whereIn('tournament_id', $tournamentIds)->count();
        $progressPercentage = $allMatchesCount > 0 ? (int) round(($completedMatchesCount / $allMatchesCount) * 100) : 0;

        return view('v1.admin.dashboard', [
            'tournaments' => $tournaments,
            'totalTeamsCount' => $totalTeamsCount,
            'activePlayersCount' => $activePlayersCount,
            'upcomingMatchesCount' => $upcomingMatchesCount,
            'pendingResultsCount' => $pendingResultsCount,
            'recentMatches' => $recentMatches,
            'progressPercentage' => $progressPercentage,
        ]);
    }
}
