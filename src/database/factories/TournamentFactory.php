<?php

namespace Database\Factories;

use App\Models\v1\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'sport_type' => fake()->randomElement(['Futbol', 'Baloncesto', 'Voleibol']),
            'start_date' => fake()->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
            'status' => 'pending',
        ];
    }
}
