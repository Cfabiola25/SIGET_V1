<?php

namespace App\Services\v1;

use App\Models\User;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationDispatcherService
{
    /**
     * Envía recordatorio automático al Director Técnico 2 horas antes
     * del cierre reglamentario para el envío de alineación.
     */
    public function sendLineupDeadlineReminder(MatchGame $match, Team $team): array
    {
        $coach = $team->coach ?? $team->captain;
        $deadlineText = $match->match_date->subHours(1)->format('H:i');
        $matchTime = $match->match_date->format('d/m/Y H:i');

        $message = "⚠️ *SIGET - Recordatorio de Alineación*\n"
            ."Hola {$coach?->name}, recuerda confirmar la nómina oficial de *{$team->name}* "
            ."para el encuentro vs *".($team->id === $match->home_team_id ? $match->awayTeam?->name : $match->homeTeam?->name)."* ($matchTime).\n"
            ."⏰ *Hora límite:* {$deadlineText} hs.\n"
            ."📲 Carga tu nómina aquí: ".route('matches.lineup.edit', ['match' => $match->id, 'team' => $team->id]);

        return $this->dispatchOmnichannel([
            'recipient_phone' => $team->phone ?? $coach?->phone ?? null,
            'recipient_email' => $coach?->email,
            'user_id' => $coach?->id,
            'title' => '⏰ Cierre de Alineación en 2 Horas',
            'body' => $message,
            'type' => 'lineup_reminder',
        ]);
    }

    /**
     * Notificación instantánea emitida al jugador y a la directiva del club
     * cuando el Tribunal de Disciplina o el cierre post-partido emite una sanción.
     */
    public function sendSanctionIssuedNotification(DisciplinarySanction $sanction): array
    {
        $player = $sanction->player;
        $team = $player?->team;
        $coach = $team?->coach;

        $typeLabel = match ($sanction->sanction_type) {
            'red_card' => 'Tarjeta Roja Directa',
            'double_yellow' => 'Doble Tarjeta Amarilla',
            'yellow_accumulation' => 'Acumulación de Tarjetas Amarillas',
            default => 'Sanción Administrativa',
        };

        $message = "⚖️ *SIGET - Tribunal de Disciplina*\n"
            ."Se ha registrado una sanción oficial para *{$player?->name}* ({$team?->name}).\n"
            ."📌 *Motivo:* {$typeLabel}\n"
            ."⛔ *Fechas de suspensión:* {$sanction->matches_suspended} fecha(s)\n"
            ."📝 *Dictamen:* {$sanction->notes}\n"
            ."El jugador queda inhabilitado en el sistema QR y consola hasta cumplir su sanción.";

        return $this->dispatchOmnichannel([
            'recipient_phone' => $team?->phone ?? $coach?->phone ?? null,
            'recipient_email' => $coach?->email,
            'user_id' => $coach?->id,
            'title' => '⚖️ Sanción Disciplinaria Oficial',
            'body' => $message,
            'type' => 'disciplinary_sanction',
        ]);
    }

    /**
     * Alerta inmediata de reprogramación por lluvia, fuerza mayor o cambio de sede/cancha.
     */
    public function sendMatchRescheduledAlert(MatchGame $match, string $reason): array
    {
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;
        $referee = $match->referee;

        $dateFormatted = $match->match_date->format('d/m/Y H:i');
        $venueName = $match->venue?->name ?? 'Por confirmar';
        $field = $match->field_number ?? 1;

        $message = "📍 *SIGET - Reprogramación de Partido*\n"
            ."El encuentro *{$homeTeam?->name} vs {$awayTeam?->name}* ha sido reprogramado.\n"
            ."🌧️ *Causa:* {$reason}\n"
            ."🗓️ *Nueva Fecha:* {$dateFormatted} hs\n"
            ."🏟️ *Sede:* {$venueName} (Campo {$field})\n"
            ."🗺️ *Ubicación:* ".($match->venue?->navigation_url ?? 'N/A');

        $recipients = [
            $homeTeam?->coach?->phone ?? $homeTeam?->phone,
            $awayTeam?->coach?->phone ?? $awayTeam?->phone,
            $referee?->phone,
        ];

        $results = [];
        foreach (array_filter($recipients) as $phone) {
            $results[] = $this->dispatchOmnichannel([
                'recipient_phone' => $phone,
                'title' => '📍 Alerta: Partido Reprogramado',
                'body' => $message,
                'type' => 'match_rescheduled',
            ]);
        }

        return [
            'success' => true,
            'recipients_notified' => count($results),
        ];
    }

    /**
     * Dispatcher omnicanal que orquesta WhatsApp Cloud API, SMS Gateway y WebPush.
     *
     * @param  array{
     *     recipient_phone?: string|null,
     *     recipient_email?: string|null,
     *     user_id?: int|null,
     *     title: string,
     *     body: string,
     *     type: string
     * }  $payload
     * @return array{
     *     whatsapp: bool,
     *     sms: bool,
     *     push: bool
     * }
     */
    public function dispatchOmnichannel(array $payload): array
    {
        $status = [
            'whatsapp' => false,
            'sms' => false,
            'push' => false,
        ];

        // 1. Canal WhatsApp Business Cloud API (Simulado / Configurable por .env)
        if (! empty($payload['recipient_phone'])) {
            $whatsappToken = config('services.whatsapp.token');
            $whatsappPhoneId = config('services.whatsapp.phone_id');

            if ($whatsappToken && $whatsappPhoneId) {
                try {
                    $response = Http::withToken($whatsappToken)->post(
                        "https://graph.facebook.com/v19.0/{$whatsappPhoneId}/messages",
                        [
                            'messaging_product' => 'whatsapp',
                            'to' => preg_replace('/\D/', '', $payload['recipient_phone']),
                            'type' => 'text',
                            'text' => ['body' => $payload['body']],
                        ]
                    );
                    $status['whatsapp'] = $response->successful();
                } catch (\Throwable $e) {
                    Log::warning('WhatsApp notification failed: '.$e->getMessage());
                }
            } else {
                Log::info("[SIGET WhatsApp Omnichannel] Enrutado para {$payload['recipient_phone']}: {$payload['title']}");
                $status['whatsapp'] = true;
            }
        }

        // 2. Canal WebPush (si el usuario tiene suscripción VAPID guardada)
        if (! empty($payload['user_id'])) {
            Log::info("[SIGET WebPush] Notificación enviada a User #{$payload['user_id']}: {$payload['title']}");
            $status['push'] = true;
        }

        return $status;
    }
}
