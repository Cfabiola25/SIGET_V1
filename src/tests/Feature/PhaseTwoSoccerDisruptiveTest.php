<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchLineup;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\TournamentRule;
use App\Services\v1\PlayerEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoSoccerDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_automatically_initializes_profile_and_medical_records(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create(['tournament_id' => $tournament->id]);

        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Luis Sinisterra',
            'identification_document' => 'CC12345678',
            'jersey_number' => 11,
        ]);

        $this->assertNotNull($player->profile);
        $this->assertNotNull($player->profile->qr_token);
        $this->assertSame('Colombiana', $player->profile->nationality);
        $this->assertStringContainsString("siget:player:{$player->id}:", $player->profile->qr_payload);

        $this->assertNotNull($player->medicalRecord);
        $this->assertSame('O+', $player->medicalRecord->blood_type);
        $this->assertTrue($player->medicalRecord->isClearedForMatch());
    }

    public function test_public_cromo_and_qr_carnet_are_accessible(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create(['tournament_id' => $tournament->id]);
        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Jhon Arias',
            'identification_document' => 'CC87654321',
            'jersey_number' => 21,
        ]);

        $this->get(route('players.cromo', $player))
            ->assertOk()
            ->assertSee('Jhon Arias')
            ->assertSee('#21')
            ->assertSee('Estadísticas Oficiales');

        $this->get(route('players.carnet', $player))
            ->assertOk()
            ->assertSee('SIGET CARNET')
            ->assertSee('HABILITADO')
            ->assertSee('<svg', false);
    }

    public function test_player_medical_record_can_be_updated_with_waiver_signature(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
        ]);
        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Jefferson Lerma',
            'identification_document' => 'CC55443322',
            'jersey_number' => 8,
        ]);

        $this->actingAs($coach)
            ->put(route('players.medical.update', $player), [
                'blood_type' => 'A+',
                'health_provider' => 'Sura EPS',
                'allergies' => 'Alergia a mariscos',
                'emergency_contact_name' => 'Laura Lerma',
                'emergency_contact_phone' => '3201234567',
                'waiver_signed' => 1,
            ])
            ->assertRedirect(route('players.cromo', $player));

        $medical = $player->fresh()->medicalRecord;
        $this->assertSame('A+', $medical->blood_type);
        $this->assertSame('Sura EPS', $medical->health_provider);
        $this->assertTrue($medical->waiver_signed);
        $this->assertNotNull($medical->waiver_signed_at);
        $this->assertTrue($medical->isClearedForMatch());
    }

    public function test_tripartite_filter_blocks_ineligible_player_with_disciplinary_sanction(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create(['tournament_id' => $tournament->id]);
        $opponent = Team::factory()->create(['tournament_id' => $tournament->id]);
        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $team->id,
            'away_team_id' => $opponent->id,
            'match_date' => now()->addDays(2),
        ]);

        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Yerry Mina',
            'identification_document' => 'CC332211',
            'jersey_number' => 13,
        ]);

        // Aplicar sanción disciplinaria por acumulación de amarillas
        DisciplinarySanction::create([
            'player_id' => $player->id,
            'tournament_id' => $tournament->id,
            'sanction_type' => 'yellow_accumulation',
            'matches_suspended' => 1,
            'matches_served' => 0,
            'status' => 'active',
        ]);

        $eligibilityService = app(PlayerEligibilityService::class);
        $check = $eligibilityService->check($player, $match);

        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('Sanción disciplinaria vigente', $check['reasons'][0]);

        // Intentar registrar alineación con este jugador suspendido
        $coach = User::factory()->create(['role' => 'captain']);
        $team->update(['captain_id' => $coach->id]);

        $this->actingAs($coach)
            ->post(route('matches.lineup.store', [$match, $team]), [
                'starters' => [$player->id],
                'substitutes' => [],
            ])
            ->assertSessionHasErrors('eligibility');

        $this->assertDatabaseMissing('match_lineups', [
            'match_id' => $match->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_financial_debt_does_not_block_lineup_when_gateway_disabled(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'payment_status' => 'overdue',
        ]);
        $opponent = Team::factory()->create(['tournament_id' => $tournament->id]);
        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $team->id,
            'away_team_id' => $opponent->id,
            'match_date' => now()->addDays(2),
        ]);

        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Daniel Muñoz',
            'identification_document' => 'CC998811',
            'jersey_number' => 2,
        ]);

        // Asegurar aval médico para verificar que el estatus financiero no bloquee
        $player->medicalRecord->update([
            'medical_clearance' => true,
            'waiver_signed' => true,
            'waiver_signed_at' => now(),
        ]);

        $eligibilityService = app(PlayerEligibilityService::class);
        $check = $eligibilityService->check($player, $match);

        $this->assertTrue($check['eligible']);
    }

    public function test_coach_can_submit_valid_digital_lineup(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
        ]);
        $opponent = Team::factory()->create(['tournament_id' => $tournament->id]);
        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $team->id,
            'away_team_id' => $opponent->id,
            'match_date' => now()->addDays(1),
        ]);

        $starter1 = Player::create(['team_id' => $team->id, 'name' => 'Titular Uno', 'identification_document' => 'DOC1', 'jersey_number' => 1]);
        $starter2 = Player::create(['team_id' => $team->id, 'name' => 'Titular Dos', 'identification_document' => 'DOC2', 'jersey_number' => 2]);
        $sub1 = Player::create(['team_id' => $team->id, 'name' => 'Suplente Uno', 'identification_document' => 'DOC3', 'jersey_number' => 12]);

        $this->actingAs($coach)
            ->post(route('matches.lineup.store', [$match, $team]), [
                'starters' => [$starter1->id, $starter2->id],
                'substitutes' => [$sub1->id],
            ])
            ->assertRedirect(route('matches.show', $match));

        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'player_id' => $starter1->id,
            'is_starter' => true,
        ]);
        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'player_id' => $sub1->id,
            'is_starter' => false,
        ]);
    }

    public function test_lineup_submission_is_locked_when_cutoff_time_expires(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $tournament = Tournament::factory()->create();
        $rules = TournamentRule::where('tournament_id', $tournament->id)->first();
        $rules->update(['lineup_lock_minutes_before_match' => 15]);

        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
        ]);
        $opponent = Team::factory()->create(['tournament_id' => $tournament->id]);

        // Partido programado dentro de 5 minutos (menos de los 15 minutos reglamentarios)
        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $team->id,
            'away_team_id' => $opponent->id,
            'match_date' => now()->addMinutes(5),
        ]);

        $player = Player::create(['team_id' => $team->id, 'name' => 'Tarde Jugador', 'identification_document' => 'DOC99', 'jersey_number' => 99]);

        $this->actingAs($coach)
            ->post(route('matches.lineup.store', [$match, $team]), [
                'starters' => [$player->id],
                'substitutes' => [],
            ])
            ->assertSessionHasErrors('lineup');

        $this->assertDatabaseCount('match_lineups', 0);
    }

    public function test_referee_can_scan_qr_carnet_and_instantly_verify_player_in_field(): void
    {
        $tournament = Tournament::factory()->create();
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => now()->addHours(1),
        ]);

        $player = Player::create([
            'team_id' => $homeTeam->id,
            'name' => 'Luis Diaz',
            'identification_document' => 'CC777777',
            'jersey_number' => 7,
        ]);

        MatchLineup::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'is_starter' => true,
            'jersey_number' => 7,
        ]);

        $refereeUser = User::factory()->create(['role' => 'admin']);

        // Escaneo del QR en cancha
        $response = $this->actingAs($refereeUser)
            ->postJson(route('referees.matches.scan.verify', $match), [
                'qr_payload' => $player->profile->qr_payload,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'authorized',
                'player' => [
                    'name' => 'Luis Diaz',
                    'jersey_number' => 7,
                    'lineup_status' => 'Titular',
                    'verified_by_qr' => true,
                ],
            ]);

        $lineup = MatchLineup::where('match_id', $match->id)->where('player_id', $player->id)->first();
        $this->assertTrue($lineup->verified_by_qr);
        $this->assertNotNull($lineup->verified_at);
    }
}
