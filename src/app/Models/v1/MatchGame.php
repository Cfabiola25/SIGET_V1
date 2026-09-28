<?php

namespace App\Models\v1;

use Database\Factories\MatchGameFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchGame extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return MatchGameFactory::new();
    }

    protected $fillable = [
        'tournament_id',
        'round_number',
        'stage',
        'bracket_position',
        'next_match_id',
        'next_match_slot',
        'group_name',
        'home_team_id',
        'away_team_id',
        'venue_id',
        'referee_id',
        'field_number',
        'match_date',
        'home_score',
        'away_score',
        'status',
        'is_locked',
        'locked_at',
        'match_sheet_notes',
        'current_period',
        'timer_started_at',
        'elapsed_seconds',
        'is_timer_running',
        'mvp_player_id',
        'chronicle_title',
        'chronicle_body',
        'chronicle_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'timer_started_at' => 'datetime',
            'locked_at' => 'datetime',
            'chronicle_generated_at' => 'datetime',
            'round_number' => 'integer',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'elapsed_seconds' => 'integer',
            'is_timer_running' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }

    public function mvpPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'mvp_player_id');
    }

    public function mvpVotes(): HasMany
    {
        return $this->hasMany(MatchMvpVote::class, 'match_id');
    }

    public function refereeEvaluations(): HasMany
    {
        return $this->hasMany(RefereeEvaluation::class, 'match_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->orderBy('minute')->orderBy('second');
    }

    public function getCurrentClockSeconds(): int
    {
        if ($this->is_timer_running && $this->timer_started_at) {
            return $this->elapsed_seconds + max(0, now()->diffInSeconds($this->timer_started_at));
        }

        return $this->elapsed_seconds;
    }

    public function getFormattedClockAttribute(): string
    {
        $sec = $this->getCurrentClockSeconds();
        $min = (int) floor($sec / 60);
        $remainder = $sec % 60;

        return sprintf('%02d:%02d', $min, $remainder);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'match_id');
    }

    public function startersForTeam(int $teamId): Collection
    {
        return $this->lineups()->where('team_id', $teamId)->where('is_starter', true)->with('player.profile')->get();
    }

    public function substitutesForTeam(int $teamId): Collection
    {
        return $this->lineups()->where('team_id', $teamId)->where('is_starter', false)->with('player.profile')->get();
    }

    public function isLineupSubmissionLocked(): bool
    {
        $lockMinutes = $this->tournament?->rules?->lineup_lock_minutes_before_match ?? 10;
        $lockTime = $this->match_date->copy()->subMinutes($lockMinutes);

        return now()->isAfter($lockTime);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(MatchSignature::class, 'match_id');
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function getSignature(string $role): ?MatchSignature
    {
        return $this->signatures()->where('signer_role', $role)->first();
    }

    public function allSignaturesCollected(): bool
    {
        $roles = $this->signatures()->pluck('signer_role')->all();

        return in_array('referee', $roles) && in_array('home_coach', $roles) && in_array('away_coach', $roles);
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_match_id');
    }

    public function previousMatches(): HasMany
    {
        return $this->hasMany(self::class, 'next_match_id');
    }

    public function isPlayoff(): bool
    {
        return $this->stage !== 'regular';
    }

    public function getWinnerTeam(): ?Team
    {
        if ($this->home_score > $this->away_score) {
            return $this->homeTeam;
        }

        if ($this->away_score > $this->home_score) {
            return $this->awayTeam;
        }

        // En caso de empate en eliminación directa, verificar si hay notas de penales o ganador definido
        return null;
    }

    public function advanceWinnerToNextMatch(): ?self
    {
        if (! $this->next_match_id || ! $this->next_match_slot) {
            return null;
        }

        $winner = $this->getWinnerTeam();
        if (! $winner) {
            return null;
        }

        $next = $this->nextMatch;
        if (! $next) {
            return null;
        }

        if ($this->next_match_slot === 'home') {
            $next->update(['home_team_id' => $winner->id]);
        } elseif ($this->next_match_slot === 'away') {
            $next->update(['away_team_id' => $winner->id]);
        }

        return $next->fresh();
    }
}
