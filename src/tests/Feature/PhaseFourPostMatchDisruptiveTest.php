<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchSignature;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Services\v1\PlayerEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourPostMatchDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_match_closure_screen_loads_with_lineups_and_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Real Madrid']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Barcelona']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 2,
            'away_score' => 1,
            'status' => 'scheduled',
            'match_date' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('matches.closure', $match))
            ->assertOk()
            ->assertSee('Cierre de Partido y Firma de Acta Digital')
            ->assertSee('Real Madrid')
            ->assertSee('Barcelona')
            ->assertSee('Árbitro Principal')
            ->assertSee('DT / Delegado Local')
            ->assertSee('DT / Delegado Visitante');
    }

    public function test_submitting_closure_saves_tripartite_signatures_and_locks_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeCoach = User::factory()->create(['name' => 'Carlo Ancelotti', 'role' => 'captain']);
        $awayCoach = User::factory()->create(['name' => 'Hansi Flick', 'role' => 'captain']);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'captain_id' => $homeCoach->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'captain_id' => $awayCoach->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 3,
            'away_score' => 0,
            'status' => 'scheduled',
            'match_date' => now(),
        ]);

        $payload = [
            'referee_name' => 'Pierluigi Collina',
            'referee_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'home_coach_name' => 'Carlo Ancelotti',
            'home_coach_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'away_coach_name' => 'Hansi Flick',
            'away_coach_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'match_sheet_notes' => 'Partido disputado con normalidad absoluta.',
        ];

        $response = $this->actingAs($admin)
            ->post(route('matches.closure.submit', $match), $payload);

        $response->assertRedirect(route('matches.report', $match));

        // Verificar firmas guardadas
        $this->assertDatabaseHas('match_signatures', [
            'match_id' => $match->id,
            'signer_role' => 'referee',
            'signer_name' => 'Pierluigi Collina',
        ]);
        $this->assertDatabaseHas('match_signatures', [
            'match_id' => $match->id,
            'signer_role' => 'home_coach',
            'signer_name' => 'Carlo Ancelotti',
        ]);
        $this->assertDatabaseHas('match_signatures', [
            'match_id' => $match->id,
            'signer_role' => 'away_coach',
            'signer_name' => 'Hansi Flick',
        ]);

        // Verificar inmutabilidad del partido
        $match->refresh();
        $this->assertTrue($match->isLocked());
        $this->assertEquals('played', $match->status);
        $this->assertNotNull($match->locked_at);
        $this->assertEquals('Partido disputado con normalidad absoluta.', $match->match_sheet_notes);
        $this->assertTrue($match->allSignaturesCollected());
    }

    public function test_inmutability_locked_match_rejects_subsequent_event_recording_and_timer_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'status' => 'played',
            'is_locked' => true,
            'locked_at' => now(),
            'match_date' => now(),
        ]);

        // 1. Intentar registrar un nuevo evento en partido bloqueado debe dar 403
        $this->actingAs($admin)
            ->postJson(route('matches.events.store', $match), [
                'team_id' => $homeTeam->id,
                'event_type' => 'goal',
            ])
            ->assertStatus(403);

        // 2. Intentar mover el cronómetro en partido bloqueado debe dar 403
        $this->actingAs($admin)
            ->postJson(route('matches.timer.update', $match), [
                'action' => 'start',
            ])
            ->assertStatus(403);

        // 3. Intentar editar marcador por controlador estándar debe dar 403
        $this->actingAs($admin)
            ->put(route('matches.update', $match), [
                'home_score' => 99,
                'away_score' => 0,
            ])
            ->assertStatus(403);
    }

    public function test_disciplinary_engine_generates_automatic_sanction_for_direct_red_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);

        // Configuración de reglas: 2 fechas por roja directa
        $tournament->rules->update([
            'direct_red_suspension_matches' => 2,
        ]);

        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $player = Player::factory()->create(['team_id' => $homeTeam->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'status' => 'scheduled',
            'match_date' => now(),
        ]);

        // Evento de Tarjeta Roja directa
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'event_type' => 'red_card',
            'minute' => 35,
            'second' => 0,
            'period' => '1H',
        ]);

        $payload = [
            'referee_name' => 'Árbitro FIFA',
            'referee_signature' => 'data:image/png;base64,sample',
            'home_coach_name' => 'DT Local',
            'home_coach_signature' => 'data:image/png;base64,sample',
            'away_coach_name' => 'DT Visitante',
            'away_coach_signature' => 'data:image/png;base64,sample',
        ];

        $this->actingAs($admin)->post(route('matches.closure.submit', $match), $payload);

        // Debe haberse generado automáticamente la sanción por roja directa
        $this->assertDatabaseHas('disciplinary_sanctions', [
            'player_id' => $player->id,
            'tournament_id' => $tournament->id,
            'match_id' => $match->id,
            'sanction_type' => 'direct_red',
            'matches_suspended' => 2,
            'matches_served' => 0,
            'status' => 'active',
        ]);

        // Comprobar con PlayerEligibilityService que el jugador queda inhabilitado
        $eligibilityService = app(PlayerEligibilityService::class);
        $nextMatch = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now()->addDays(7),
        ]);

        $check = $eligibilityService->check($player, $nextMatch);
        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('expulsión con tarjeta roja directa', $check['reasons'][0]);
    }

    public function test_disciplinary_engine_generates_automatic_sanction_for_yellow_card_accumulation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);

        // Límite de 2 amarillas para suspensión
        $tournament->rules->update([
            'yellow_card_limit_for_suspension' => 2,
        ]);

        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $player = Player::factory()->create(['team_id' => $homeTeam->id]);

        // Partido 1 previo: el jugador ya recibió su 1ª tarjeta amarilla
        $match1 = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'status' => 'played',
            'match_date' => now()->subDays(7),
        ]);
        MatchEvent::create([
            'match_id' => $match1->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'event_type' => 'yellow_card',
            'minute' => 20,
            'second' => 0,
            'period' => '1H',
        ]);

        // Partido 2: el jugador recibe su 2ª tarjeta amarilla en el torneo
        $match2 = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'status' => 'scheduled',
            'match_date' => now(),
        ]);
        MatchEvent::create([
            'match_id' => $match2->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'event_type' => 'yellow_card',
            'minute' => 60,
            'second' => 0,
            'period' => '2H',
        ]);

        $payload = [
            'referee_name' => 'Árbitro Principal',
            'referee_signature' => 'data:image/png;base64,sample',
            'home_coach_name' => 'DT Local',
            'home_coach_signature' => 'data:image/png;base64,sample',
            'away_coach_name' => 'DT Visitante',
            'away_coach_signature' => 'data:image/png;base64,sample',
        ];

        $this->actingAs($admin)->post(route('matches.closure.submit', $match2), $payload);

        // Se debe haber generado la sanción por acumulación de amarillas
        $this->assertDatabaseHas('disciplinary_sanctions', [
            'player_id' => $player->id,
            'tournament_id' => $tournament->id,
            'match_id' => $match2->id,
            'sanction_type' => 'yellow_accumulation',
            'matches_suspended' => 1,
            'status' => 'active',
        ]);

        // Verificamos inhabilitación con PlayerEligibilityService
        $eligibilityService = app(PlayerEligibilityService::class);
        $match3 = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now()->addDays(7),
        ]);

        $check = $eligibilityService->check($player, $match3);
        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('acumulación de tarjetas amarillas', $check['reasons'][0]);
    }

    public function test_standings_are_automatically_recalculated_on_match_closure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Arsenal']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Chelsea']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 3,
            'away_score' => 1,
            'status' => 'scheduled',
            'match_date' => now(),
        ]);

        $payload = [
            'referee_name' => 'Árbitro Premier',
            'referee_signature' => 'data:image/png;base64,sample',
            'home_coach_name' => 'Mikel Arteta',
            'home_coach_signature' => 'data:image/png;base64,sample',
            'away_coach_name' => 'Enzo Maresca',
            'away_coach_signature' => 'data:image/png;base64,sample',
        ];

        $this->actingAs($admin)->post(route('matches.closure.submit', $match), $payload);

        // Verificar que la tabla de posiciones tiene 3 puntos para Arsenal y 0 para Chelsea
        $this->assertDatabaseHas('standings', [
            'tournament_id' => $tournament->id,
            'team_id' => $homeTeam->id,
            'matches_played' => 1,
            'wins' => 1,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 3,
            'goals_against' => 1,
            'points' => 3,
        ]);

        $this->assertDatabaseHas('standings', [
            'tournament_id' => $tournament->id,
            'team_id' => $awayTeam->id,
            'matches_played' => 1,
            'wins' => 0,
            'draws' => 0,
            'losses' => 1,
            'goals_for' => 1,
            'goals_against' => 3,
            'points' => 0,
        ]);
    }

    public function test_official_match_sheet_renders_with_embedded_signatures_and_verification_hash(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Boca Juniors']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'River Plate']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 2,
            'away_score' => 2,
            'status' => 'played',
            'is_locked' => true,
            'locked_at' => now(),
            'match_date' => now(),
        ]);

        // Registrar firmas
        MatchSignature::create([
            'match_id' => $match->id,
            'signer_role' => 'referee',
            'signer_name' => 'Néstor Pitana',
            'signature_data' => 'data:image/png;base64,ref_sig',
            'signed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('matches.report', $match))
            ->assertOk()
            ->assertSee('Acta Oficial Digital de Partido')
            ->assertSee('Boca Juniors')
            ->assertSee('River Plate')
            ->assertSee('Néstor Pitana')
            ->assertSee('HASH CRIPTOGRÁFICO DE VERIFICACIÓN')
            ->assertSee('CERRADA E INMUTABLE');
    }

    public function test_disciplinary_tribunal_dashboard_displays_sanctions_and_allows_pardon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $team = Team::factory()->create(['tournament_id' => $tournament->id]);
        $player = Player::factory()->create(['team_id' => $team->id, 'name' => 'Lionel Messi']);

        $sanction = DisciplinarySanction::create([
            'player_id' => $player->id,
            'tournament_id' => $tournament->id,
            'sanction_type' => 'direct_red',
            'matches_suspended' => 1,
            'matches_served' => 0,
            'status' => 'active',
            'notes' => 'Falta antideportiva.',
        ]);

        // Ver tribunal
        $this->actingAs($admin)
            ->get(route('tournaments.disciplinary', $tournament))
            ->assertOk()
            ->assertSee('Tribunal de Penas y Disciplina')
            ->assertSee('Lionel Messi')
            ->assertSee('Roja Directa');

        // Indultar sanción
        $this->actingAs($admin)
            ->post(route('tournaments.disciplinary.pardon', [$tournament, $sanction]))
            ->assertRedirect(route('tournaments.disciplinary', $tournament));

        $sanction->refresh();
        $this->assertEquals('pardoned', $sanction->status);
        $this->assertFalse($sanction->isActive());
    }
}
