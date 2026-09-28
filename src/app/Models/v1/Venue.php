<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_user_id',
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'maps_url',
        'field_count',
        'surface_type',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'field_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }

    public function getNavigationUrlAttribute(): ?string
    {
        if ($this->maps_url) {
            return $this->maps_url;
        }

        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}";
        }

        if ($this->address) {
            $query = urlencode("{$this->name}, {$this->address}, {$this->city}");

            return "https://www.google.com/maps/search/?api=1&query={$query}";
        }

        return null;
    }
}
