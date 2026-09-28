<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchLineup;
use App\Models\v1\MatchMvpVote;
use App\Models\v1\Player;
use App\Models\v1\Referee;
use App\Models\v1\RefereeConflictRecord;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Services\v1\PostMatchClosureService;
use App\Services\v1\RefereeAssignmentEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextGenViralAiScoutingDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_media_card_engine_renders_preview_and_downloads_assets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id, 'name' => 'Champions League Amateur']);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Galácticos FC']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Furia Roja']);

        $player = Player::factory()->create(['team_id' => $homeTeam->id, 'name' => 'Vinicius Junior']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 3,
            'away_score' => 1,
            'mvp_player_id' => $player->id,
            'status' => 'played',
            'match_date' => now(),
        ]);

        MatchLineup::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'jersey_number' => 7,
            'position' => 'forward',
            'is_starter' => true,
        ]);

        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $player->id,
            'event_type' => 'goal',
            'minute' => 23,
            'second' => 0,
        ]);

        // 1. Preview Final Score
        $previewRes = $this->get(route('matches.social_card.preview', [$match, 'type' => 'final_score', 'format' => 'feed']));
        $previewRes->assertOk()
            ->assertSee('Generador Visual de Assets para Redes')
            ->assertSee('Galácticos FC')
            ->assertSee('Furia Roja')
            ->assertSee('Champions League Amateur');

        // 2. Download Final Score Feed
        $downloadRes = $this->get(route('matches.social_card.download', [$match, 'type' => 'final_score', 'format' => 'feed']));
        $downloadRes->assertOk();
        $this->assertContains($downloadRes->headers->get('Content-Type'), ['image/svg+xml', 'image/png']);

        // 3. Download Lineup Story
        $lineupRes = $this->get(route('matches.social_card.download', [$match, 'type' => 'lineup', 'format' => 'story', 'team_id' => $homeTeam->id]));
        $lineupRes->assertOk();

        // 4. Download MVP Story
        $mvpRes = $this->get(route('matches.social_card.download', [$match, 'type' => 'mvp', 'format' => 'story']));
        $mvpRes->assertOk();

        // 5. Raw SVG Endpoint
        $rawRes = $this->get(route('matches.social_card.raw', [$match, 'type' => 'final_score']));
        $rawRes->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_live_public_fan_mvp_voting_registers_vote_and_updates_live_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Inter']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Milan']);

        $player1 = Player::factory()->create(['team_id' => $homeTeam->id, 'name' => 'Lautaro Martínez']);
        $player2 = Player::factory()->create(['team_id' => $awayTeam->id, 'name' => 'Rafael Leão']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'status' => 'scheduled',
            'is_locked' => false,
            'match_date' => now(),
        ]);

        // 1. Fan vota por Lautaro
        $voteRes = $this->withHeaders(['X-Voter-Fingerprint' => 'fan-session-12345'])
            ->postJson(route('matches.mvp.vote', $match), [
                'player_id' => $player1->id,
            ]);

        $voteRes->assertOk()
            ->assertJson([
                'success' => true,
                'message' => '¡Tu voto para el MVP del partido ha sido registrado con éxito!',
            ]);

        $this->assertDatabaseHas('match_mvp_votes', [
            'match_id' => $match->id,
            'player_id' => $player1->id,
            'voter_fingerprint' => 'fan-session-12345',
        ]);

        // 2. Mismo aficionado cambia su voto por Leão (uniqueness per fingerprint)
        $changeVoteRes = $this->withHeaders(['X-Voter-Fingerprint' => 'fan-session-12345'])
            ->postJson(route('matches.mvp.vote', $match), [
                'player_id' => $player2->id,
            ]);

        $changeVoteRes->assertOk();
        $this->assertEquals(1, MatchMvpVote::where('match_id', $match->id)->count());
        $this->assertEquals($player2->id, MatchMvpVote::where('match_id', $match->id)->first()->player_id);

        // 3. Segundo aficionado vota por Lautaro
        $this->withHeaders(['X-Voter-Fingerprint' => 'fan-session-67890'])
            ->postJson(route('matches.mvp.vote', $match), [
                'player_id' => $player1->id,
            ])
            ->assertOk();

        // 4. Consultar live stats de votación
        $statsRes = $this->getJson(route('matches.mvp.stats', $match));
        $statsRes->assertOk()
            ->assertJsonStructure([
                'match_id',
                'is_locked',
                'official_mvp',
                'stats' => [
                    '*' => ['player_id', 'player_name', 'team_id', 'votes_count', 'percentage'],
                ],
            ]);

        $this->assertCount(2, $statsRes->json('stats'));
    }

    public function test_post_match_closure_automatically_crowns_mvp_and_generates_ai_sports_chronicle(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id, 'name' => 'Copa de Campeones']);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Boca Juniors']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'River Plate']);

        $mvpCandidate = Player::factory()->create(['team_id' => $homeTeam->id, 'name' => 'Edinson Cavani']);
        $otherPlayer = Player::factory()->create(['team_id' => $awayTeam->id, 'name' => 'Miguel Borja']);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_score' => 2,
            'away_score' => 1,
            'status' => 'scheduled',
            'is_locked' => false,
            'match_date' => now(),
        ]);

        MatchLineup::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $mvpCandidate->id,
            'jersey_number' => 10,
            'is_starter' => true,
        ]);

        // Registrar goles de Cavani
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $mvpCandidate->id,
            'event_type' => 'goal',
            'minute' => 45,
            'second' => 0,
        ]);
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $homeTeam->id,
            'player_id' => $mvpCandidate->id,
            'event_type' => 'goal',
            'minute' => 88,
            'second' => 0,
        ]);

        // Fan votes
        MatchMvpVote::create([
            'match_id' => $match->id,
            'player_id' => $mvpCandidate->id,
            'voter_fingerprint' => 'fan-1',
        ]);
        MatchMvpVote::create([
            'match_id' => $match->id,
            'player_id' => $mvpCandidate->id,
            'voter_fingerprint' => 'fan-2',
        ]);

        // Cerrar el partido con el servicio oficial de cierre
        $closureService = app(PostMatchClosureService::class);
        $result = $closureService->closeMatch($match, 'Partido disputado con máxima intensidad deportiva.');

        $this->assertTrue($result['success']);

        $match->refresh();

        // 1. Verifica coronación de MVP
        $this->assertEquals($mvpCandidate->id, $match->mvp_player_id);
        $this->assertGreaterThanOrEqual(1, $mvpCandidate->profile->fresh()->mvp_awards_count);

        // 2. Verifica generación de crónica deportiva periodística IA
        $this->assertNotNull($match->chronicle_title);
        $this->assertNotNull($match->chronicle_body);
        $this->assertNotNull($match->chronicle_generated_at);
        $this->assertStringContainsString('Boca Juniors', $match->chronicle_body);
        $this->assertStringContainsString('River Plate', $match->chronicle_body);
        $this->assertStringContainsString('Edinson Cavani', $match->chronicle_body);

        // 3. Verifica visualización en la vista oficial de partido
        $this->actingAs($admin)
            ->get(route('matches.show', $match))
            ->assertOk()
            ->assertSee('OFICIAL MVP OF THE MATCH')
            ->assertSee('Edinson Cavani')
            ->assertSee('IA MATCH REPORTER')
            ->assertSee($match->chronicle_title);
    }

    public function test_free_agency_transfer_market_and_talent_radar_scouting(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
            'name' => 'Atlético Nacional',
        ]);

        // Crear jugador agente libre (team_id = null, is_free_agent = true)
        $freeAgent = Player::create([
            'name' => 'James Rodríguez',
            'identification_document' => 'CC-1098765432',
            'team_id' => null,
            'jersey_number' => null,
        ]);
        $profile = $freeAgent->profile;
        $profile->update([
            'position' => 'midfielder',
            'preferred_foot' => 'left',
            'is_free_agent' => true,
            'performance_rating' => 9.2,
            'mvp_awards_count' => 5,
            'scouting_notes' => 'Enganche clásico de visión periférica élite y pegada milimétrica con zurda.',
        ]);

        // 1. Consultar el mercado de agentes libres con filtros
        $response = $this->get(route('scouting.index', [
            'position' => 'midfielder',
            'preferred_foot' => 'left',
            'min_rating' => '8.0',
        ]));

        $response->assertOk()
            ->assertSee('Radar de Talentos')
            ->assertSee('James Rodríguez')
            ->assertSee('Agente Libre')
            ->assertSee('9.2');

        // 2. Consultar la ficha técnica individual con radar de rendimiento
        $detailRes = $this->get(route('scouting.show', $freeAgent));
        $detailRes->assertOk()
            ->assertSee('James Rodríguez')
            ->assertSee('Pentágono de Rendimiento')
            ->assertSee('Enganche clásico de visión periférica');

        // 3. DT recluta formalmente al agente libre para su club
        $recruitRes = $this->actingAs($coach)->post(route('scouting.recruit', $freeAgent), [
            'team_id' => $team->id,
            'jersey_number' => 10,
        ]);

        $recruitRes->assertRedirect(route('scouting.index'))
            ->assertSessionHas('status');

        $freeAgent->refresh();
        $this->assertEquals($team->id, $freeAgent->team_id);
        $this->assertEquals(10, $freeAgent->jersey_number);
        $this->assertFalse((bool) $freeAgent->profile->is_free_agent);

        // 4. Liberar al jugador de nuevo a la agencia libre
        $releaseRes = $this->actingAs($coach)->post(route('scouting.release', $freeAgent), [
            'scouting_notes' => 'Liberado de común acuerdo tras cumplir ciclo formativo.',
        ]);

        $releaseRes->assertRedirect();
        $freeAgent->refresh();
        $this->assertNull($freeAgent->team_id);
        $this->assertTrue((bool) $freeAgent->profile->is_free_agent);
    }

    public function test_algorithmic_referee_assignment_avoids_conflicts_of_interest(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Chelsea']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Arsenal']);

        // Árbitro A: Rating alto (4.95), pero con conflicto con Chelsea
        $refereeA = Referee::create([
            'name' => 'Pierluigi Collina',
            'license_number' => 'FIFA-001',
            'is_active' => true,
            'rating_average' => 4.95,
            'total_matches_officiated' => 10,
        ]);
        RefereeConflictRecord::create([
            'referee_id' => $refereeA->id,
            'team_id' => $homeTeam->id,
            'reason' => 'Incidente disciplinario previo y recusa formal del club',
        ]);

        // Árbitro B: Rating bueno (4.70), sin conflicto
        $refereeB = Referee::create([
            'name' => 'Howard Webb',
            'license_number' => 'FIFA-002',
            'is_active' => true,
            'rating_average' => 4.70,
            'total_matches_officiated' => 8,
        ]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'referee_id' => null,
            'match_date' => now()->addDays(2),
        ]);

        $engine = app(RefereeAssignmentEngine::class);
        $assignedReferee = $engine->assignReferee($match);

        $this->assertNotNull($assignedReferee);
        // Debe ignorar al Árbitro A por conflicto de interés y designar al Árbitro B
        $this->assertEquals($refereeB->id, $assignedReferee->id);
        $this->assertEquals($refereeB->id, $match->fresh()->referee_id);
    }

    public function test_post_match_referee_evaluation_by_dt_updates_referee_average(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create(['admin_id' => $admin->id]);
        $homeTeam = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
            'name' => 'Juventus',
        ]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Milan']);

        $referee = Referee::create([
            'name' => 'Nestor Pitana',
            'license_number' => 'CONMEBOL-77',
            'is_active' => true,
            'rating_average' => 5.00,
            'total_matches_officiated' => 1,
        ]);

        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'referee_id' => $referee->id,
            'status' => 'played',
            'match_date' => now()->subDay(),
        ]);

        // El DT de Juventus envía su evaluación post-partido
        $evalRes = $this->actingAs($coach)->post(route('matches.referee.evaluate', $match), [
            'team_id' => $homeTeam->id,
            'score_overall' => 4,
            'score_rule_enforcement' => 4,
            'score_fairness' => 5,
            'score_punctuality' => 5,
            'comments' => 'Excelente manejo de las protestas y buena fluidez de juego.',
        ]);

        $evalRes->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('referee_evaluations', [
            'match_id' => $match->id,
            'referee_id' => $referee->id,
            'team_id' => $homeTeam->id,
            'score_overall' => 4,
        ]);

        $referee->refresh();
        $this->assertEquals(4.00, $referee->rating_average);

        // Verificación de visualización en el panel arbitral
        $this->actingAs($admin)->get(route('referees.evaluations.index'))
            ->assertOk()
            ->assertSee('Panel de Arbitraje Profesional')
            ->assertSee('Nestor Pitana')
            ->assertSee('4.00 ★');
    }
}
