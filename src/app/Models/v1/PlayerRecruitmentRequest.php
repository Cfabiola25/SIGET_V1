<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRecruitmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'team_id',
        'requested_by_user_id',
        'proposed_jersey_number',
        'contact_phone',
        'contact_email',
        'message',
        'status',
        'notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_jersey_number' => 'integer',
            'responded_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
