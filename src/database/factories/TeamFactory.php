<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'captain_id' => User::factory()->state(['role' => 'captain']),
            'name' => fake()->unique()->company().' FC',
            'logo_path' => null,
            'status' => 'approved',
        ];
    }
}
