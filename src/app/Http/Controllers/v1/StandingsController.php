<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Standings;

class StandingsController extends Controller
{
    public function index()
    {
        return view('v1.standings.index', ['standings' => Standings::with(['team', 'tournament'])->orderByDesc('points')->get()]);
    }
}
