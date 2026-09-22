<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    public function index()
    {
        return view('v1.players.index');
    }
}
