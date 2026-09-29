<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function show()
    {
        // Total stats
        $totalMatches = MatchGame::count();
        $totalGoals = MatchEvent::where('event_type', 'goal')->count();
        $totalYellowCards = MatchEvent::where('event_type', 'yellow_card')->count();
        $totalRedCards = MatchEvent::where('event_type', 'red_card')->count();
        $goalsPerMatch = $totalMatches > 0 ? round($totalGoals / $totalMatches, 2) : 0;

        // Top Scorers
        $topScorers = Player::with('team')
            ->withCount(['events as goals_count' => function ($query) {
                $query->where('event_type', 'goal');
            }])
            ->orderByDesc('goals_count')
            ->take(5)
            ->get();

        // Top Sanctioned Players
        $topCarded = Player::with('team')
            ->withCount(['events as yellow_cards' => function ($query) {
                $query->where('event_type', 'yellow_card');
            }])
            ->withCount(['events as red_cards' => function ($query) {
                $query->where('event_type', 'red_card');
            }])
            ->having('yellow_cards', '>', 0)
            ->orHaving('red_cards', '>', 0)
            ->orderByDesc('red_cards')
            ->orderByDesc('yellow_cards')
            ->take(5)
            ->get();

        // Top Teams by Goals Scored
        $teamsWithGoals = Team::withCount('players')->take(5)->get();

        return view('v1.statistics.show', compact(
            'totalMatches',
            'totalGoals',
            'totalYellowCards',
            'totalRedCards',
            'goalsPerMatch',
            'topScorers',
            'topCarded',
            'teamsWithGoals'
        ));
    }
}
