<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['name', 'season', 'status'];
}
