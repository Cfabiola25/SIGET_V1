<?php

namespace Tests\Feature;

use App\Events\v1\MatchEventOccurred;
use App\Events\v1\MatchTimerUpdated;
use App\Models\User;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PhaseThreeMatchDayDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_referee_can_access_match_day_console(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['super_admin_id' => $superAdmin->id, 'admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('matches.console', $match))
            ->assertOk()
            ->assertSee('Consola Oficial de Arbitraje')
            ->assertSee($homeTeam->name)
            ->assertSee($awayTeam->name)
            ->assertSee('GOL')
            ->assertSee('AMARILLA')
            ->assertSee('ROJA');
    }

    public function test_referee_can_control_match_stopwatch(): void
    {
        Event::fake([MatchTimerUpdated::class]);

        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now(),
            'current_period' => 'scheduled',
            'is_timer_running' => false,
        ]);

        // 1. Iniciar Primer Tiempo
        $resStart = $this->actingAs($admin)
            ->postJson(route('matches.timer.update', $match), ['action' => 'start_1h']);

        $resStart->assertOk()
            ->assertJson([
                'success' => true,
                'current_period' => 'first_half',
                'is_timer_running' => true,
            ]);

        $this->assertTrue($match->fresh()->is_timer_running);
        $this->assertSame('first_half', $match->fresh()->current_period);
        Event::assertDispatched(MatchTimerUpdated::class);

        // 2. Pausar
        $resPause = $this->actingAs($admin)
            ->postJson(route('matches.timer.update', $match), ['action' => 'pause']);

        $resPause->assertOk()->assertJson(['is_timer_running' => false]);
        $this->assertFalse($match->fresh()->is_timer_running);

        // 3. Entretiempo
        $this->actingAs($admin)
            ->postJson(route('matches.timer.update', $match), ['action' => 'halftime'])
            ->assertOk()
            ->assertJson(['current_period' => 'halftime']);

        $this->assertSame('halftime', $match->fresh()->current_period);
    }

    public function test_recording_goal_event_automatically_updates_scoreboard_and_broadcasts(): void
    {
        Event::fake([MatchEventOccurred::class]);

        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 0,
            'away_score' => 0,
            'match_date' => now(),
            'current_period' => 'first_half',
        ]);

        $scorer = Player::create([
            'team_id' => $homeTeam->id,
            'name' => 'James Rodriguez',
            'identification_document' => 'DOC10',
            'jersey_number' => 10,
        ]);

        // Registrar Gol a los 23 minutos y 15 segundos
        $response = $this->actingAs($admin)
            ->postJson(route('matches.events.store', $match), [
                'team_id' => $homeTeam->id,
                'event_type' => 'goal',
                'player_id' => $scorer->id,
                'minute' => 23,
                'second' => 15,
                'notes' => 'Tiro libre al ángulo superior izquierdo',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'home_score' => 1,
                'away_score' => 0,
                'event' => [
                    'player_name' => 'James Rodriguez',
                    'event_type' => 'goal',
                    'icon' => '⚽',
                ],
            ]);

        // El marcador en la base de datos subió a 1
        $this->assertSame(1, $match->fresh()->home_score);
        $this->assertSame(0, $match->fresh()->away_score);

        $this->assertDatabaseHas('match_events', [
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $scorer->id,
            'event_type' => 'goal',
            'minute' => 23,
            'second' => 15,
        ]);

        Event::assertDispatched(MatchEventOccurred::class);
    }

    public function test_recording_yellow_card_and_substitution_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now(),
            'current_period' => 'second_half',
        ]);

        $subOut = Player::create(['team_id' => $awayTeam->id, 'name' => 'Saliente', 'identification_document' => 'DOC1', 'jersey_number' => 7]);
        $subIn = Player::create(['team_id' => $awayTeam->id, 'name' => 'Entrante', 'identification_document' => 'DOC2', 'jersey_number' => 18]);

        // Registrar sustitución
        $this->actingAs($admin)
            ->postJson(route('matches.events.store', $match), [
                'team_id' => $awayTeam->id,
                'event_type' => 'substitution',
                'player_id' => $subOut->id,
                'sub_in_player_id' => $subIn->id,
                'minute' => 65,
                'second' => 30,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'event' => [
                    'event_type' => 'substitution',
                    'icon' => '🔄',
                    'player_name' => 'Saliente',
                    'sub_in_player_name' => 'Entrante',
                ],
            ]);

        $this->assertDatabaseHas('match_events', [
            'match_id' => $match->id,
            'player_id' => $subOut->id,
            'sub_in_player_id' => $subIn->id,
            'event_type' => 'substitution',
            'period' => '2H',
        ]);
    }

    public function test_public_live_feed_endpoint_returns_match_state(): void
    {
        $tournament = Tournament::factory()->create();
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Once Caldas']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Deportivo Cali']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 2,
            'away_score' => 1,
            'current_period' => 'second_half',
            'is_timer_running' => true,
            'elapsed_seconds' => 3600, // 60 mins
        ]);

        $scorer = Player::create(['team_id' => $homeTeam->id, 'name' => 'Dayro Moreno', 'identification_document' => 'DOC17', 'jersey_number' => 17]);
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $scorer->id,
            'event_type' => 'goal',
            'minute' => 12,
            'second' => 45,
            'period' => '1H',
        ]);

        $response = $this->getJson(route('matches.live.feed', $match));

        $response->assertOk()
            ->assertJson([
                'match_id' => $match->id,
                'home_team' => 'Once Caldas',
                'away_team' => 'Deportivo Cali',
                'home_score' => 2,
                'away_score' => 1,
                'current_period' => 'second_half',
                'is_timer_running' => true,
            ]);

        $this->assertCount(1, $response->json('events'));
        $this->assertSame('Dayro Moreno', $response->json('events.0.player_name'));
        $this->assertSame('⚽', $response->json('events.0.icon'));
    }
}
