<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class MatchController extends Controller
{
    public function index()
    {
        return view('v1.matches.index');
    }

    public function live()
    {
        return view('v1.matches.live');
    }
}
