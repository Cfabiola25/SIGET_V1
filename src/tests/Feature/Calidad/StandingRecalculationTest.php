<?php

namespace Tests\Feature\Calidad;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guía 11 – Pruebas de regresión del recálculo de la tabla de posiciones.
 * Componente intervenido: App\Services\v1\StandingService (Mejora 1).
 */
class StandingRecalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tournament $tournament;

    private Team $home;

    private Team $away;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->tournament = Tournament::factory()->create(['admin_id' => $this->admin->id]);
        $this->home = Team::factory()->create(['tournament_id' => $this->tournament->id]);
        $this->away = Team::factory()->create(['tournament_id' => $this->tournament->id]);
    }

    /** CP-01: una victoria local suma los puntos estándar (3-0) y los goles a cada equipo. */
    public function test_home_win_assigns_standard_points_and_goals(): void
    {
        $match = $this->scheduledMatch();

        $this->actingAs($this->admin)
            ->put(route('matches.update', $match), ['home_score' => 2, 'away_score' => 1])
            ->assertRedirect(route('matches.show', $match));

        $this->assertStanding($this->home, played: 1, wins: 1, draws: 0, losses: 0, gf: 2, ga: 1, points: 3);
        $this->assertStanding($this->away, played: 1, wins: 0, draws: 0, losses: 1, gf: 1, ga: 2, points: 0);
    }

    /** CP-02: un empate suma un punto a cada equipo. */
    public function test_draw_assigns_one_point_to_each_team(): void
    {
        $match = $this->scheduledMatch();

        $this->actingAs($this->admin)
            ->put(route('matches.update', $match), ['home_score' => 1, 'away_score' => 1]);

        $this->assertStanding($this->home, played: 1, wins: 0, draws: 1, losses: 0, gf: 1, ga: 1, points: 1);
        $this->assertStanding($this->away, played: 1, wins: 0, draws: 1, losses: 0, gf: 1, ga: 1, points: 1);
    }

    /**
     * CP-03 (regla de negocio): la tabla debe respetar los puntos configurados en el reglamento
     * del torneo (tournament_rules). Antes de la refactorización el servicio usaba los valores
     * fijos 3/1/0 y esta prueba fallaba.
     */
    public function test_recalculation_uses_points_configured_in_tournament_rules(): void
    {
        $this->tournament->rules()->update([
            'points_for_win' => 2,
            'points_for_draw' => 1,
            'points_for_loss' => 1,
        ]);
        $match = $this->scheduledMatch();

        $this->actingAs($this->admin)
            ->put(route('matches.update', $match), ['home_score' => 3, 'away_score' => 0]);

        $this->assertStanding($this->home, played: 1, wins: 1, draws: 0, losses: 0, gf: 3, ga: 0, points: 2);
        $this->assertStanding($this->away, played: 1, wins: 0, draws: 0, losses: 1, gf: 0, ga: 3, points: 1);
    }

    /** CP-04: los partidos no jugados no afectan la tabla y todos los equipos quedan registrados. */
    public function test_only_played_matches_are_counted(): void
    {
        $third = Team::factory()->create(['tournament_id' => $this->tournament->id]);
        MatchGame::factory()->create([
            'tournament_id' => $this->tournament->id,
            'home_team_id' => $third->id,
            'away_team_id' => $this->away->id,
            'home_score' => 5,
            'away_score' => 0,
            'status' => 'scheduled',
        ]);
        $match = $this->scheduledMatch();

        $this->actingAs($this->admin)
            ->put(route('matches.update', $match), ['home_score' => 0, 'away_score' => 2]);

        $this->assertStanding($this->away, played: 1, wins: 1, draws: 0, losses: 0, gf: 2, ga: 0, points: 3);
        $this->assertStanding($third, played: 0, wins: 0, draws: 0, losses: 0, gf: 0, ga: 0, points: 0);
    }

    private function scheduledMatch(): MatchGame
    {
        return MatchGame::factory()->create([
            'tournament_id' => $this->tournament->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'status' => 'scheduled',
        ]);
    }

    private function assertStanding(Team $team, int $played, int $wins, int $draws, int $losses, int $gf, int $ga, int $points): void
    {
        $this->assertDatabaseHas('standings', [
            'tournament_id' => $this->tournament->id,
            'team_id' => $team->id,
            'matches_played' => $played,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $gf,
            'goals_against' => $ga,
            'points' => $points,
        ]);
    }
}
