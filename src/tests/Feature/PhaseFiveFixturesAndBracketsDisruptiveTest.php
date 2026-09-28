<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Standings;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use App\Services\v1\BracketService;
use App\Services\v1\FixtureGeneratorService;
use App\Services\v1\PostMatchClosureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveFixturesAndBracketsDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_berger_algorithm_generates_correct_round_robin_for_even_and_odd_teams(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = Team::factory()->count(4)->create(['tournament_id' => $tournament->id]);

        $service = app(FixtureGeneratorService::class);

        // 4 Equipos (Par) -> 3 Jornadas, 6 partidos totales
        $preview = $service->preview($tournament, ['rounds_count' => 1]);
        $this->assertEquals(3, $preview['total_rounds']);
        $this->assertEquals(6, $preview['total_matches']);

        // 2 Vueltas (Ida y Vuelta) -> 6 Jornadas, 12 partidos totales
        $previewDouble = $service->preview($tournament, ['rounds_count' => 2]);
        $this->assertEquals(6, $previewDouble['total_rounds']);
        $this->assertEquals(12, $previewDouble['total_matches']);

        // 5 Equipos (Impar) -> Se agrega BYE, 5 Jornadas, 10 partidos totales (cada equipo descansa 1 jornada)
        $extraTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $previewOdd = $service->preview($tournament, ['rounds_count' => 1]);
        $this->assertEquals(5, $previewOdd['total_rounds']);
        $this->assertEquals(10, $previewOdd['total_matches']);
    }

    public function test_fixture_generator_respects_cross_constraints_venues_and_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $venue = Venue::create([
            'created_by_user_id' => $admin->id,
            'name' => 'Complejo Deportivo Camp Nou',
            'address' => 'Av. Deporte 100',
            'city' => 'Bogotá',
            'field_count' => 2,
            'is_active' => true,
        ]);

        Team::factory()->count(4)->create(['tournament_id' => $tournament->id]);

        $startDate = now()->addDays(5)->startOfDay()->addHours(10);

        $service = app(FixtureGeneratorService::class);
        $matches = $service->generateAndPersist($tournament, [
            'start_date' => $startDate->toDateTimeString(),
            'days_between_rounds' => 7,
            'rounds_count' => 1,
            'venue_ids' => [$venue->id],
        ]);

        $this->assertCount(6, $matches);

        // Verificar que los partidos tienen asignada la sede y fechas incrementales de 7 días
        $firstRoundMatches = $matches->where('round_number', 1);
        $secondRoundMatches = $matches->where('round_number', 2);

        $this->assertCount(2, $firstRoundMatches);
        $this->assertCount(2, $secondRoundMatches);

        foreach ($matches as $match) {
            $this->assertEquals($venue->id, $match->venue_id);
            $this->assertEquals('regular', $match->stage);
            $this->assertEquals('scheduled', $match->status);
        }

        // Verificar diferencia de 7 días entre ronda 1 y ronda 2
        $r1Date = $firstRoundMatches->first()->match_date;
        $r2Date = $secondRoundMatches->first()->match_date;
        $this->assertEquals(7, $r1Date->diffInDays($r2Date));
    }

    public function test_fixture_generator_controller_generates_and_persists_matches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        Team::factory()->count(4)->create(['tournament_id' => $tournament->id]);

        $this->actingAs($admin)
            ->get(route('tournaments.fixtures.generate', $tournament))
            ->assertOk()
            ->assertSee('Generador Automático de Calendario')
            ->assertSee('Motor Matemático Berger');

        $payload = [
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'days_between_rounds' => 7,
            'rounds_count' => 1,
            'time_slots' => ['10:00', '12:00'],
        ];

        $response = $this->actingAs($admin)
            ->post(route('tournaments.fixtures.generate.submit', $tournament), $payload);

        $response->assertRedirect(route('tournaments.show', $tournament));
        $this->assertDatabaseCount('match_games', 6);
    }

    public function test_bracket_service_transitions_top_four_teams_to_semifinals_and_final(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = Team::factory()->count(4)->create(['tournament_id' => $tournament->id]);

        // Simular tabla de posiciones: Equipos 0, 1, 2, 3 clasificados en orden 1º, 2º, 3º, 4º
        foreach ($teams as $index => $team) {
            Standings::create([
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'matches_played' => 3,
                'wins' => 3 - $index,
                'draws' => 0,
                'losses' => $index,
                'goals_for' => (4 - $index) * 2,
                'goals_against' => $index,
                'points' => (3 - $index) * 3,
            ]);
        }

        $bracketService = app(BracketService::class);
        $result = $bracketService->transitionFromStandings($tournament, [
            'bracket_size' => 4,
            'days_between_stages' => 7,
        ]);

        $this->assertEquals(4, $result['bracket_size']);
        $this->assertEquals(3, $result['total_matches_created']); // 2 SF + 1 Final

        // SF1: 1º vs 4º
        $sf1 = MatchGame::where('bracket_position', 'SF1')->first();
        $this->assertNotNull($sf1);
        $this->assertEquals($teams[0]->id, $sf1->home_team_id);
        $this->assertEquals($teams[3]->id, $sf1->away_team_id);
        $this->assertEquals('semifinals', $sf1->stage);

        // SF2: 2º vs 3º
        $sf2 = MatchGame::where('bracket_position', 'SF2')->first();
        $this->assertNotNull($sf2);
        $this->assertEquals($teams[1]->id, $sf2->home_team_id);
        $this->assertEquals($teams[2]->id, $sf2->away_team_id);

        // Gran Final: Sin equipos asignados aún, pero enlazada a las semifinales
        $final = MatchGame::where('bracket_position', 'FINAL')->first();
        $this->assertNotNull($final);
        $this->assertNull($final->home_team_id);
        $this->assertNull($final->away_team_id);

        $this->assertEquals($final->id, $sf1->next_match_id);
        $this->assertEquals('home', $sf1->next_match_slot);
        $this->assertEquals($final->id, $sf2->next_match_id);
        $this->assertEquals('away', $sf2->next_match_slot);
    }

    public function test_bracket_service_transitions_top_eight_teams_to_quarterfinals(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = Team::factory()->count(8)->create(['tournament_id' => $tournament->id]);

        foreach ($teams as $index => $team) {
            Standings::create([
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'matches_played' => 5,
                'wins' => 7 - $index,
                'draws' => 0,
                'losses' => $index,
                'goals_for' => (8 - $index) * 2,
                'goals_against' => $index,
                'points' => (7 - $index) * 3,
            ]);
        }

        $bracketService = app(BracketService::class);
        $result = $bracketService->transitionFromStandings($tournament, [
            'bracket_size' => 8,
        ]);

        $this->assertEquals(8, $result['bracket_size']);
        $this->assertEquals(7, $result['total_matches_created']); // 4 QF + 2 SF + 1 Final

        // QF1: 1º vs 8º
        $qf1 = MatchGame::where('bracket_position', 'QF1')->first();
        $this->assertEquals($teams[0]->id, $qf1->home_team_id);
        $this->assertEquals($teams[7]->id, $qf1->away_team_id);

        // QF2: 4º vs 5º
        $qf2 = MatchGame::where('bracket_position', 'QF2')->first();
        $this->assertEquals($teams[3]->id, $qf2->home_team_id);
        $this->assertEquals($teams[4]->id, $qf2->away_team_id);
    }

    public function test_closing_playoff_match_automatically_advances_winner_to_next_round(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Boca Juniors']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'River Plate']);

        // Crear Gran Final vacía
        $final = MatchGame::create([
            'tournament_id' => $tournament->id,
            'stage' => 'final',
            'bracket_position' => 'FINAL',
            'match_date' => now()->addDays(7),
            'status' => 'scheduled',
            'home_team_id' => null,
            'away_team_id' => null,
        ]);

        // Crear Semifinal 1 con Boca vs River enlazada a la final como slot "home"
        $sf1 = MatchGame::create([
            'tournament_id' => $tournament->id,
            'stage' => 'semifinals',
            'bracket_position' => 'SF1',
            'match_date' => now(),
            'status' => 'scheduled',
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 2,
            'away_score' => 1,
            'next_match_id' => $final->id,
            'next_match_slot' => 'home',
        ]);

        // Cerrar el partido mediante PostMatchClosureService
        $closureService = app(PostMatchClosureService::class);
        $result = $closureService->closeMatch($sf1, 'Semifinal apasionante.');

        $this->assertTrue($result['success']);

        // Verificar que Boca Juniors (ganador) avanzó automáticamente a la Gran Final como home_team
        $final->refresh();
        $this->assertEquals($homeTeam->id, $final->home_team_id);
        $this->assertNull($final->away_team_id);
    }

    public function test_brackets_controller_renders_visual_bracket_tree(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $teams = Team::factory()->count(4)->create(['tournament_id' => $tournament->id]);

        // Sin llaves creadas aún -> Muestra asistente de creación
        $this->actingAs($admin)
            ->get(route('tournaments.brackets', $tournament))
            ->assertOk()
            ->assertSee('Transición a Fase de Playoffs')
            ->assertSee('Activar Llaves de Eliminación Directa');

        // Crear llaves
        $this->actingAs($admin)
            ->post(route('tournaments.brackets.generate', $tournament), [
                'bracket_size' => 4,
            ])
            ->assertRedirect(route('tournaments.brackets', $tournament));

        // Con llaves creadas -> Muestra el árbol de eliminación visual
        $this->actingAs($admin)
            ->get(route('tournaments.brackets', $tournament))
            ->assertOk()
            ->assertSee('Árbol de Eliminación Activo')
            ->assertSee('Semifinales')
            ->assertSee('Gran Final de Campeonato');
    }
}
