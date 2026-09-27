<?php

namespace Database\Factories;

use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchGame>
 */
class MatchGameFactory extends Factory
{
    protected $model = MatchGame::class;

    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'match_date' => fake()->dateTimeBetween('+1 day', '+2 months'),
            'home_score' => 0,
            'away_score' => 0,
            'status' => 'scheduled',
        ];
    }
}
