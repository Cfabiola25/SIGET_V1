<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefereeConflictRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'referee_id',
        'team_id',
        'reason',
    ];

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
