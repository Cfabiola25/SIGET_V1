<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinarySanction extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'tournament_id',
        'match_id',
        'sanction_type',
        'matches_suspended',
        'matches_served',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'matches_suspended' => 'integer',
            'matches_served' => 'integer',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->matches_served < $this->matches_suspended;
    }

    public function remainingMatches(): int
    {
        return max(0, $this->matches_suspended - $this->matches_served);
    }
}
