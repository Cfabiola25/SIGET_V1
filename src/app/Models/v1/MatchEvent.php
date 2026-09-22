<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class MatchEvent extends Model
{
    protected $fillable = ['match_id', 'team_id', 'player_id', 'event_type', 'minute'];
}
