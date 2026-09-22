<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class MatchGame extends Model
{
    protected $fillable = ['tournament_id', 'home_team_id', 'away_team_id', 'played_at', 'status'];
}
