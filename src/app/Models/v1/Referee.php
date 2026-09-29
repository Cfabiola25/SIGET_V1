<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'is_active',
        'rating_average',
        'total_matches_officiated',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'rating_average' => 'float',
            'total_matches_officiated' => 'integer',
        ];
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(RefereeEvaluation::class);
    }

    public function conflictRecords(): HasMany
    {
        return $this->hasMany(RefereeConflictRecord::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'referee_id');
    }

    public function hasConflictWithTeam(int $teamId): bool
    {
        return $this->conflictRecords()->where('team_id', $teamId)->exists();
    }

    public function recalculateRatingAverage(): void
    {
        $avg = $this->evaluations()->avg('score_overall');
        $this->rating_average = $avg ? round($avg, 2) : 5.00;
        $this->total_matches_officiated = $this->matches()->where('status', 'finished')->count();
        $this->save();
    }
}
