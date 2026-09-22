<?php

namespace App\Services\v1;

class StatisticsService
{
    public function getSummary(): array
    {
        return [
            'matches' => 0,
            'teams' => 0,
            'players' => 0,
        ];
    }
}
