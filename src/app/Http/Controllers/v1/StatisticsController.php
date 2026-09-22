<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class StatisticsController extends Controller
{
    public function show()
    {
        return view('v1.statistics.show');
    }
}
