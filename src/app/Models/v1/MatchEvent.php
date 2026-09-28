<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'sub_in_player_id',
        'event_type',
        'period',
        'minute',
        'second',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'minute' => 'integer',
            'second' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function subInPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'sub_in_player_id');
    }

    public function getFormattedTimeAttribute(): string
    {
        return sprintf("%02d'%02d\"", $this->minute, $this->second);
    }

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            'goal' => '⚽',
            'yellow_card' => '🟨',
            'red_card' => '🟥',
            'substitution' => '🔄',
            'injury' => '🩹',
            default => '⏱️',
        };
    }

    public function getLabelAttribute(): string
    {
        return match ($this->event_type) {
            'goal' => 'Gol',
            'yellow_card' => 'Tarjeta Amarilla',
            'red_card' => 'Tarjeta Roja',
            'substitution' => 'Sustitución',
            'injury' => 'Atención Médica / Lesión',
            default => 'Incidencia',
        };
    }
}
