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
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'height_cm' => 'integer',
            'weight_kg' => 'integer',
            'mvp_count' => 'integer',
        ];
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
