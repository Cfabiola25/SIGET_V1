<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\v1\Player;
use App\Models\v1\Referee;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CAPTAIN = 'captain';

    public const ROLE_COACH = 'coach';

    /** Roles con acceso al backoffice administrativo. */
    public const STAFF_ROLES = [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN];

    /** Roles que actúan como Director Técnico de un equipo. */
    public const DT_ROLES = [self::ROLE_CAPTAIN, self::ROLE_COACH];

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
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Personal administrativo: super administrador o administrador de torneo. */
    public function isStaff(): bool
    {
        return in_array($this->role, self::STAFF_ROLES, true);
    }

    /** Puede inscribir equipos: personal administrativo o capitán. */
    public function canRegisterTeams(): bool
    {
        return $this->isStaff() || $this->role === self::ROLE_CAPTAIN;
    }

    public function isCaptain(): bool
    {
        return $this->isDT();
    }

    public function isCoach(): bool
    {
        return $this->isDT();
    }

    public function isDT(): bool
    {
        return in_array($this->role, self::DT_ROLES, true);
    }

    public function hasManagedTournaments(): bool
    {
        return $this->managedTournaments()->exists();
    }

    public function hasManagedTeams(): bool
    {
        return $this->managedTeams()->exists();
    }

    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

    public function isReferee(): bool
    {
        return $this->role === 'referee';
    }

    public function refereeProfile(): HasOne
    {
        return $this->hasOne(Referee::class);
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
