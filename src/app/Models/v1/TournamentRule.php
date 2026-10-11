<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'yellow_card_limit_for_suspension',
        'direct_red_suspension_matches',
        'points_for_win',
        'points_for_draw',
        'points_for_loss',
        'match_duration_minutes',
        'max_substitutions',
        'tiebreaker_rule',
        'reset_cards_on_knockout',
        'lineup_lock_minutes_before_match',
    ];

    protected function casts(): array
    {
        return [
            'yellow_card_limit_for_suspension' => 'integer',
            'direct_red_suspension_matches' => 'integer',
            'points_for_win' => 'integer',
            'points_for_draw' => 'integer',
            'points_for_loss' => 'integer',
            'match_duration_minutes' => 'integer',
            'max_substitutions' => 'integer',
            'reset_cards_on_knockout' => 'boolean',
            'lineup_lock_minutes_before_match' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public static function defaultRulesForFootball(int $tournamentId): array
    {
        return self::rulesForModality('Fútbol 11', $tournamentId);
    }

    public static function rulesForModality(string $modality, int $tournamentId): array
    {
        $base = [
            'tournament_id' => $tournamentId,
            'yellow_card_limit_for_suspension' => 2,
            'direct_red_suspension_matches' => 1,
            'points_for_win' => 3,
            'points_for_draw' => 1,
            'points_for_loss' => 0,
            'tiebreaker_rule' => 'goal_difference',
            'reset_cards_on_knockout' => false,
            'lineup_lock_minutes_before_match' => 10,
        ];

        return match (true) {
            str_contains($modality, '8') => array_merge($base, [
                'match_duration_minutes' => 60,
                'max_substitutions' => 7,
            ]),
            str_contains($modality, '5') => array_merge($base, [
                'match_duration_minutes' => 50,
                'max_substitutions' => 8,
            ]),
            str_contains($modality, 'Salón') || str_contains($modality, 'Micro') => array_merge($base, [
                'match_duration_minutes' => 40,
                'max_substitutions' => 10,
            ]),
            default => array_merge($base, [ // Fútbol 11
                'match_duration_minutes' => 90,
                'max_substitutions' => 5,
            ]),
        };
    }
}
