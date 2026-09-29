<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected $attributes = ['is_active' => true];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function createdTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'super_admin_id');
    }

    public function managedTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'admin_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCaptain(): bool
    {
        return in_array($this->role, ['captain', 'coach'], true);
    }

    public function isCoach(): bool
    {
        return in_array($this->role, ['captain', 'coach'], true);
    }

    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

    public function isReferee(): bool
    {
        return $this->role === 'referee';
    }

    public function refereeProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\v1\Referee::class);
    }

    public function captainedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'captain_id');
    }

    public function managedTeams(): HasMany
    {
        return $this->captainedTeams();
    }

    public function playerProfiles(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
