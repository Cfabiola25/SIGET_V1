<?php

namespace App\Models\v1;

use App\Models\User;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return PlayerFactory::new();
    }

    protected $fillable = ['team_id', 'user_id', 'name', 'identification_document', 'jersey_number'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(PlayerProfile::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(PlayerMedicalRecord::class);
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class);
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(DisciplinarySanction::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function hasActiveSanctionInTournament(?int $tournamentId = null): bool
    {
        $query = $this->sanctions()->where('status', 'active');
        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        return $query->whereColumn('matches_served', '<', 'matches_suspended')->exists();
    }

    public function goalsCount(): int
    {
        return $this->events()->where('event_type', 'goal')->count();
    }

    public function yellowCardsCount(): int
    {
        return $this->events()->where('event_type', 'yellow_card')->count();
    }

    public function redCardsCount(): int
    {
        return $this->events()->where('event_type', 'red_card')->count();
    }

    protected static function booted(): void
    {
        static::created(function (Player $player): void {
            if (! $player->profile()->exists()) {
                PlayerProfile::createDefault($player->id);
            }
            if (! $player->medicalRecord()->exists()) {
                PlayerMedicalRecord::createDefault($player->id);
            }
        });
    }
}
