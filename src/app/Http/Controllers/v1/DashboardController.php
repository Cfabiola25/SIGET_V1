<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('v1.dashboard');
    }
}
