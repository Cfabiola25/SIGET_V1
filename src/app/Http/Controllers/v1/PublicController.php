<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class PublicController extends Controller
{
    public function home()
    {
        return view('v1.public.home');
    }

    public function tournament()
    {
        return view('v1.public.tournament');
    }
}
