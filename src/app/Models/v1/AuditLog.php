<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'details'];
}
