<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefereeEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'referee_id',
        'team_id',
        'evaluated_by_user_id',
        'score_overall',
        'score_rule_enforcement',
        'score_fairness',
        'score_punctuality',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'score_overall' => 'integer',
            'score_rule_enforcement' => 'integer',
            'score_fairness' => 'integer',
            'score_punctuality' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by_user_id');
    }
}
