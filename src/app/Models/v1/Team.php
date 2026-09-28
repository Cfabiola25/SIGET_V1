<?php

namespace App\Models\v1;

use App\Models\User;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return TeamFactory::new();
    }

    protected $fillable = ['tournament_id', 'captain_id', 'name', 'logo_path', 'status', 'payment_status'];

    public function isRosterLockedForDebt(): bool
    {
        return false;
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function coach(): BelongsTo
    {
        return $this->captain();
    }

    public function getCoachNameAttribute(): string
    {
        return $this->captain?->name ?? 'Director Técnico';
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function activeInvitation(): HasOne
    {
        return $this->hasOne(TeamInvitation::class)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->captain_id === $user->id;
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'away_team_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class);
    }
}
