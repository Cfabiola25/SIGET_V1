<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function index()
    {
        return view('v1.tournaments.index');
    }
}
