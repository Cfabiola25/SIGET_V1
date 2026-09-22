<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name', 'short_name', 'country'];
}
