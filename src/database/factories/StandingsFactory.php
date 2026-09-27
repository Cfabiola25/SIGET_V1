<?php

namespace Database\Factories;

use App\Models\v1\Standings;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Standings>
 */
class StandingsFactory extends Factory
{
    protected $model = Standings::class;

    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'team_id' => Team::factory(),
            'matches_played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ];
    }
}
