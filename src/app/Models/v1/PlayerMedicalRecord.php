<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'blood_type',
        'health_provider',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'waiver_signed',
        'waiver_signed_at',
        'id_document_path',
        'is_medically_cleared',
    ];

    protected function casts(): array
    {
        return [
            'waiver_signed' => 'boolean',
            'is_medically_cleared' => 'boolean',
            'waiver_signed_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function isClearedForMatch(): bool
    {
        return $this->waiver_signed && $this->is_medically_cleared;
    }

    public static function createDefault(int $playerId): self
    {
        return self::create([
            'player_id' => $playerId,
            'blood_type' => 'O+',
            'waiver_signed' => true,
            'waiver_signed_at' => now(),
            'is_medically_cleared' => true,
        ]);
    }
}
