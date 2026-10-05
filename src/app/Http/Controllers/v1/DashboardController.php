<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isReferee()) {
            return redirect()->route('referees.portal');
        }

        $baseData = [
            'tournamentsCount' => Tournament::count(),
            'teamsCount' => Team::count(),
            'matchesCount' => \App\Models\v1\MatchGame::count(),
            'venuesCount' => \App\Models\v1\Venue::count(),
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
