<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLineup extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'is_starter',
        'jersey_number',
        'position',
        'verified_by_qr',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'verified_by_qr' => 'boolean',
            'verified_at' => 'datetime',
            'jersey_number' => 'integer',
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

    public function markVerified(): void
    {
        $this->update([
            'verified_by_qr' => true,
            'verified_at' => now(),
        ]);
    }
}
