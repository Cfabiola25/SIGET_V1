<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class TournamentController extends Controller
{
    public function index()
    {
        return view('v1.tournaments.index');
    }

    public function show($id)
    {
        return view('v1.tournaments.show', ['id' => $id]);
    }
}
