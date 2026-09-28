<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PlayerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'photo_path',
        'position',
        'preferred_foot',
        'birth_date',
        'nationality',
        'height_cm',
        'weight_kg',
        'mvp_count',
        'is_free_agent',
        'performance_rating',
        'mvp_awards_count',
        'scouting_notes',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'height_cm' => 'integer',
            'weight_kg' => 'integer',
            'mvp_count' => 'integer',
            'is_free_agent' => 'boolean',
            'performance_rating' => 'float',
            'mvp_awards_count' => 'integer',
        ];
    }

    public function scopeFreeAgents($query)
    {
        return $query->where('is_free_agent', true);
    }

    public function recalculatePerformanceRating(): float
    {
        $base = 6.5;

        // Goals count
        $goals = $this->player?->events()->where('event_type', 'goal')->count() ?? 0;
        $mvps = $this->mvp_awards_count ?: ($this->mvp_count ?: 0);
        $redCards = $this->player?->events()->where('event_type', 'red_card')->count() ?? 0;
        $yellowCards = $this->player?->events()->where('event_type', 'yellow_card')->count() ?? 0;

        $score = $base + ($goals * 0.4) + ($mvps * 0.5) - ($redCards * 0.6) - ($yellowCards * 0.15);
        $rating = (float) max(1.0, min(10.0, round($score, 1)));

        $this->performance_rating = $rating;
        $this->save();

        return $rating;
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getPositionLabelAttribute(): string
    {
        return match ($this->position) {
            'goalkeeper' => 'Portero',
            'defender' => 'Defensa',
            'forward' => 'Delantero',
            default => 'Mediocampista',
        };
    }

    public function getPreferredFootLabelAttribute(): string
    {
        return match ($this->preferred_foot) {
            'left' => 'Zurdo',
            'ambidextrous' => 'Ambidiestro',
            default => 'Diestro',
        };
    }

    public function getQrPayloadAttribute(): string
    {
        return "siget:player:{$this->player_id}:{$this->qr_token}";
    }

    public static function createDefault(int $playerId, string $position = 'midfielder'): self
    {
        return self::create([
            'player_id' => $playerId,
            'position' => $position,
            'preferred_foot' => 'right',
            'nationality' => 'Colombiana',
            'mvp_count' => 0,
            'qr_token' => Str::random(40),
        ]);
    }
}
