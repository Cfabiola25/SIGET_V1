<?php

namespace Database\Factories;

use App\Models\v1\Player;
use App\Models\v1\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'identification_document' => fake()->unique()->numerify('##########'),
            'jersey_number' => fake()->numberBetween(1, 99),
        ];
    }
}
