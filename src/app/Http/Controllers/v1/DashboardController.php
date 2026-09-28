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

        $data = match ($user->role) {
            'admin' => ['tournamentsCount' => Tournament::count(), 'teamsCount' => Team::count()],
            'organizer' => ['tournaments' => $user->tournaments()->latest()->get()],
            'captain' => ['teams' => $user->captainedTeams()->with('tournament')->get()],
            default => ['players' => $user->playerProfiles()->with('team')->get()],
        };

        return view('v1.dashboard', $data);
    }
}
