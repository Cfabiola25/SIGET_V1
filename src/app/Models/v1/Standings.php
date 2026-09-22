<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Standings extends Model
{
    protected $fillable = ['tournament_id', 'team_id', 'played', 'won', 'drawn', 'lost', 'points'];
}
