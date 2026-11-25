<?php

namespace Tests\Feature\Calidad;

use App\Models\User;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guía 11 – Pruebas de los componentes en los que se eliminó código duplicado (Mejora 4):
 * MatchDayController::matchStatePayload() y ScoutingController::availableTeamsFor().
 */
class MatchLiveFeedAndScoutingTest extends TestCase
{
    use RefreshDatabase;

    /** CP-14: el feed JSON en vivo expone marcador, equipos y eventos del partido. */
    public function test_live_feed_returns_match_state_with_events(): void
    {
        [$match, $player] = $this->matchWithGoal();

        $this->getJson(route('matches.live.feed', $match))
            ->assertOk()
            ->assertJsonPath('match_id', $match->id)
            ->assertJsonPath('home_team', $match->homeTeam->name)
            ->assertJsonPath('away_team', $match->awayTeam->name)
            ->assertJsonPath('home_score', 1)
            ->assertJsonPath('away_score', 0)
            ->assertJsonPath('events.0.event_type', 'goal')
            ->assertJsonPath('events.0.player_name', $player->name)
            ->assertJsonStructure(['status', 'current_period', 'is_timer_running', 'formatted_clock', 'elapsed_seconds']);
    }

    /** CP-15: el stream SSE emite el mismo estado del partido en formato event-stream. */
    public function test_sse_stream_emits_same_match_state(): void
    {
        [$match, $player] = $this->matchWithGoal();

        $response = $this->get(route('matches.stream', $match));
        $body = $response->streamedContent();

        $response->assertOk();
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith("event: match_state\ndata: ", $body);

        $data = json_decode(trim(substr($body, strpos($body, 'data: ') + 6)), true);
        $this->assertSame($match->id, $data['match_id']);
        $this->assertSame(1, $data['home_score']);
        $this->assertSame($player->name, $data['events'][0]['player_name']);
    }

    /** CP-16: en el radar administrativo, un administrador solo ve los equipos de sus torneos. */
    public function test_admin_scouting_lists_only_teams_from_managed_tournaments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $own = Tournament::factory()->create(['admin_id' => $admin->id]);
        $ownTeam = Team::factory()->create(['tournament_id' => $own->id]);
        $foreignTeam = Team::factory()->create(['tournament_id' => Tournament::factory()->create()->id]);

        $teams = $this->actingAs($admin)
            ->get(route('admin.scouting.index'))
            ->assertOk()
            ->viewData('availableTeams');

        $this->assertTrue($teams->contains('id', $ownTeam->id));
        $this->assertFalse($teams->contains('id', $foreignTeam->id));
    }

    /** @return array{MatchGame, Player} */
    private function matchWithGoal(): array
    {
        $match = MatchGame::factory()->create(['home_score' => 1, 'away_score' => 0, 'status' => 'live']);
        $player = Player::factory()->create(['team_id' => $match->home_team_id]);
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $match->home_team_id,
            'player_id' => $player->id,
            'event_type' => 'goal',
            'period' => 'first_half',
            'minute' => 12,
        ]);

        return [$match, $player];
    }
}
