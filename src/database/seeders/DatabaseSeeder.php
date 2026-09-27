<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Standings;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin SIGET',
            'email' => 'superadmin@siget.com',
            'role' => 'super_admin',
        ]);

        $admins = User::factory()->createMany([
            [
                'name' => 'Admin 1 SIGET',
                'email' => 'admin1@siget.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Admin 2 SIGET',
                'email' => 'admin2@siget.com',
                'role' => 'admin',
            ],
        ]);

        $captains = User::factory()->createMany([
            ['name' => 'Capitan 1', 'email' => 'captain1@siget.com', 'role' => 'captain'],
            ['name' => 'Capitan 2', 'email' => 'captain2@siget.com', 'role' => 'captain'],
            ['name' => 'Capitan 3', 'email' => 'captain3@siget.com', 'role' => 'captain'],
            ['name' => 'Capitan 4', 'email' => 'captain4@siget.com', 'role' => 'captain'],
        ]);

        $tournamentA = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admins[0]->id,
            'name' => 'Torneo A',
            'sport_type' => 'Futbol',
            'status' => 'active',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(5)->toDateString(),
        ]);

        Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admins[1]->id,
            'name' => 'Torneo B',
            'sport_type' => 'Futbol',
            'status' => 'pending',
            'start_date' => now()->addWeeks(6)->toDateString(),
            'end_date' => now()->addWeeks(10)->toDateString(),
        ]);

        $teamNames = ['Halcones FC', 'Titanes United', 'Atlas Deportivo', 'Costa Norte'];
        $teams = collect($teamNames)->map(function (string $teamName, int $index) use ($captains, $tournamentA) {
            return Team::factory()->create([
                'tournament_id' => $tournamentA->id,
                'captain_id' => $captains[$index]->id,
                'name' => $teamName,
                'status' => 'approved',
            ]);
        });

        $matchDate = now()->addWeek()->setTime(18, 0);

        for ($homeIndex = 0; $homeIndex < $teams->count(); $homeIndex++) {
            for ($awayIndex = $homeIndex + 1; $awayIndex < $teams->count(); $awayIndex++) {
                MatchGame::factory()->create([
                    'tournament_id' => $tournamentA->id,
                    'home_team_id' => $teams[$homeIndex]->id,
                    'away_team_id' => $teams[$awayIndex]->id,
                    'match_date' => $matchDate->copy()->addDays(($homeIndex + $awayIndex) * 2),
                    'status' => 'scheduled',
                ]);
            }
        }

        $teams->each(fn (Team $team) => Standings::factory()->create([
            'tournament_id' => $tournamentA->id,
            'team_id' => $team->id,
        ]));
    }
}
