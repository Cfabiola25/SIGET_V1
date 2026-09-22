<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['name', 'position', 'team_id'];
}
