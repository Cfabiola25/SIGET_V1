<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isReferee()) {
            return redirect()->route('referees.portal');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isDT() && $user->managedTeams()->doesntExist()) {
            return view('v1.dt.unassigned', compact('user'));
        }

        if ($user->isPlayer()) {
            $player = $user->playerProfiles()->with(['team.tournament', 'profile', 'medicalRecord'])->first();
            $team = $player?->team;
            $upcomingMatches = collect();
            if ($team) {
                $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam', 'venue', 'referee'])
                    ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
                    ->where('status', 'scheduled')
                    ->orderBy('match_date')
                    ->take(5)
                    ->get();
            }

            return view('v1.dashboard', [
                'user' => $user,
                'player' => $player,
                'team' => $team,
                'upcomingMatches' => $upcomingMatches,
            ]);
        }

        $baseData = [
            'tournamentsCount' => Tournament::count(),
            'teamsCount' => Team::count(),
            'matchesCount' => MatchGame::count(),
            'venuesCount' => Venue::count(),
        ];

        $roleData = match ($user->role) {
            'admin' => [],
            'organizer' => ['tournaments' => $user->tournaments()->latest()->get()],
            'captain', 'coach' => ['teams' => $user->managedTeams()->with('tournament')->get()],
            default => ['players' => $user->playerProfiles()->with('team')->get()],
        };

        return view('v1.dashboard', array_merge($baseData, $roleData));
    }
}
