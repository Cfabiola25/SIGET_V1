<?php

namespace App\Models\v1;

use Database\Factories\StandingsFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standings extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return StandingsFactory::new();
    }

    protected $fillable = [
        'tournament_id',
        'team_id',
        'matches_played',
        'wins',
        'draws',
        'losses',
        'goals_for',
        'goals_against',
        'points',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getPlayedAttribute(): int
    {
        return (int) ($this->attributes['matches_played'] ?? 0);
    }

    public function getWonAttribute(): int
    {
        return (int) ($this->attributes['wins'] ?? 0);
    }

    public function getDrawnAttribute(): int
    {
        return (int) ($this->attributes['draws'] ?? 0);
    }

    public function getLostAttribute(): int
    {
        return (int) ($this->attributes['losses'] ?? 0);
    }

    public function getGoalDifferenceAttribute(): int
    {
        return (int) (($this->attributes['goals_for'] ?? 0) - ($this->attributes['goals_against'] ?? 0));
    }
}
