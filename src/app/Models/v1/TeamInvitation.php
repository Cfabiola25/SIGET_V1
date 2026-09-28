<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'invited_by_user_id',
        'token',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'expires_at',
        'accepted_at',
        'claimed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public static function createForTeam(Team $team, ?User $invitedBy = null, ?string $recipientName = null, ?string $recipientEmail = null, ?string $recipientPhone = null, int $daysValid = 7): self
    {
        return self::create([
            'team_id' => $team->id,
            'invited_by_user_id' => $invitedBy?->id,
            'token' => Str::random(48),
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'recipient_phone' => $recipientPhone,
            'expires_at' => now()->addDays($daysValid),
        ]);
    }

    public function getClaimUrl(): string
    {
        return route('teams.invitations.claim', $this->token);
    }

    public function getWhatsAppShareUrl(): string
    {
        $teamName = $this->team?->name ?? 'tu equipo';
        $tournamentName = $this->team?->tournament?->name ?? 'el torneo';
        $message = "¡Hola! Has sido invitado a gestionar el equipo *{$teamName}* en *{$tournamentName}* dentro de SIGET. Accede a tu panel de Director Técnico aquí: ".$this->getClaimUrl();

        return 'https://api.whatsapp.com/send?'.($this->recipient_phone ? 'phone='.preg_replace('/\D/', '', $this->recipient_phone).'&' : '').'text='.rawurlencode($message);
    }
}
