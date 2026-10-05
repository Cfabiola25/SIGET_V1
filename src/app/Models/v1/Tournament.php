<?php

namespace App\Models\v1;

use App\Models\User;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tournament extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return TournamentFactory::new();
    }

    protected $fillable = ['super_admin_id', 'admin_id', 'name', 'sport_type', 'start_date', 'end_date', 'status', 'is_featured'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Obtiene el Torneo Principal establecido por el Super Admin (o el más reciente activo por defecto).
     */
    public static function getFeatured(): ?self
    {
        return static::where('is_featured', true)->first()
            ?? static::whereIn('status', ['active', 'pending'])->latest()->first()
            ?? static::latest()->first();
    }

    /**
     * Establece este torneo como el único Principal / Destacado.
     */
    public function makeFeatured(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['is_featured' => false]);
        $this->update(['is_featured' => true]);
    }

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class);
    }

    public function rules(): HasOne
    {
        return $this->hasOne(TournamentRule::class);
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(DisciplinarySanction::class);
    }

    protected static function booted(): void
    {
        static::created(function (Tournament $tournament): void {
            if (! $tournament->rules()->exists()) {
                $tournament->rules()->create(TournamentRule::rulesForModality($tournament->sport_type, $tournament->id));
            }
        });
    }

}
