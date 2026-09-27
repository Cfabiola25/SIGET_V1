<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('v1.admin.dashboard', [
            'tournaments' => $request->user()->managedTournaments()->withCount(['teams', 'matches'])->latest()->get(),
        ]);
    }
}
