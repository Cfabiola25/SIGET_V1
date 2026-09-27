<?php

namespace App\Models\v1;

use App\Models\User;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
