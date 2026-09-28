<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'signer_role',
        'signer_name',
        'signature_data',
        'signed_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->signer_role) {
            'referee' => 'Árbitro Principal',
            'home_coach' => 'Director Técnico Local',
            'away_coach' => 'Director Técnico Visitante',
            default => 'Firmante Oficial',
        };
    }
}
