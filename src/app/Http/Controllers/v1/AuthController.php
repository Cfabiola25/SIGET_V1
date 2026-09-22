<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login()
    {
        return view('v1.auth.login');
    }
}
