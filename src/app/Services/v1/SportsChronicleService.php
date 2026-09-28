<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SportsChronicleService
{
    /**
     * Genera y persiste una crónica deportiva profesional para el partido.
     *
     * @return array{title: string, body: string, generated_at: string}
     */
    public function generateAndSave(MatchGame $match): array
    {
        $chronicle = $this->generateChronicle($match);

        $match->update([
            'chronicle_title' => $chronicle['title'],
            'chronicle_body' => $chronicle['body'],
            'chronicle_generated_at' => now(),
        ]);

        return $chronicle;
    }

    /**
     * Genera la crónica a través de LLM o del motor narrativo algorítmico integrado.
     *
     * @return array{title: string, body: string, generated_at: string}
     */
    public function generateChronicle(MatchGame $match): array
    {
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        $openAiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');

        if ($geminiKey && ! app()->environment('testing')) {
            try {
                $llmResult = $this->callGeminiApi($match, $geminiKey);
                if ($llmResult) {
                    return $llmResult;
                }
            } catch (\Throwable $e) {
                Log::warning('Fallo llamada Gemini AI para crónica, recurriendo a motor local: '.$e->getMessage());
            }
        }

        if ($openAiKey && ! app()->environment('testing')) {
            try {
                $llmResult = $this->callOpenAiApi($match, $openAiKey);
                if ($llmResult) {
                    return $llmResult;
                }
            } catch (\Throwable $e) {
                Log::warning('Fallo llamada OpenAI para crónica, recurriendo a motor local: '.$e->getMessage());
            }
        }

        return $this->generateAlgorithmicChronicle($match);
    }

    /**
     * Motor narrativo algorítmico de alta fidelidad periodística.
     * Analiza el drama táctico, goles, tarjetas, remontadas y MVP.
     */
    public function generateAlgorithmicChronicle(MatchGame $match): array
    {
        $homeTeam = $match->homeTeam?->name ?? 'Equipo Local';
        $awayTeam = $match->awayTeam?->name ?? 'Equipo Visitante';
        $homeScore = (int) $match->home_score;
        $awayScore = (int) $match->away_score;
        $venue = $match->venue?->name ?? 'la Cancha Principal';
        $tournament = $match->tournament?->name ?? 'Torneo Oficial';
        $round = $match->round_number ? "Jornada {$match->round_number}" : ($match->stage ?? 'Fase Regular');

        // Extraer eventos
        $events = $match->events()->with('player')->get();
        $goalEvents = $events->where('event_type', 'goal');
        $redCards = $events->where('event_type', 'red_card');
        $mvp = $match->mvpPlayer;

        // Analizar la narrativa
        $totalGoals = $homeScore + $awayScore;
        $isDraw = $homeScore === $awayScore;
        $diff = abs($homeScore - $awayScore);
        $winner = $homeScore > $awayScore ? $homeTeam : ($awayScore > $homeScore ? $awayTeam : null);
        $loser = $homeScore > $awayScore ? $awayTeam : ($awayScore > $homeScore ? $homeTeam : null);

        // Detectar gol agónico (> 80 min)
        $lateGoal = $goalEvents->first(fn ($e) => ($e->minute ?? 0) >= 80);

        // Generar Titular
        if ($isDraw) {
            if ($totalGoals === 0) {
                $title = "Pacto de Acero en {$venue}: {$homeTeam} y {$awayTeam} no ceden terreno en un duelo táctico (0-0)";
            } else {
                $title = "Batalla sin tregua: Emocionante empate {$homeScore}-{$awayScore} entre {$homeTeam} y {$awayTeam}";
            }
        } elseif ($diff >= 3) {
            $title = "¡Cátedra y Contundencia! {$winner} arrolla a {$loser} ({$homeScore}-{$awayScore}) con una exhibición ofensiva";
        } elseif ($lateGoal) {
            $scorer = $lateGoal->player?->name ?? 'el artillero';
            $title = "¡Agonía y Gloria en el minuto {$lateGoal->minute}! {$winner} se impone {$homeScore}-{$awayScore} ante {$loser}";
        } else {
            $title = "Golpe de Autoridad: {$winner} se lleva un vibrante triunfo ({$homeScore}-{$awayScore}) frente a {$loser}";
        }

        // Párrafo 1: El Escenario y Contexto
        $p1 = "En una jornada vibrante y de altísima intensidad deportiva, {$homeTeam} y {$awayTeam} midieron fuerzas sobre el césped de {$venue} en el marco de la {$round} de {$tournament}. El pitazo inicial desató una auténtica pugna táctica donde ambos elencos salieron decididos a imponer sus credenciales y no regalar un solo centímetro.";

        // Párrafo 2: Los Goles y Momentos Clave
        if ($goalEvents->isNotEmpty()) {
            $goalList = [];
            foreach ($goalEvents as $g) {
                $pName = $g->player?->name ?? 'Rematador';
                $min = $g->minute ? "al minuto {$g->minute}'" : 'en una gran jugada';
                $goalList[] = "{$pName} {$min}";
            }
            $goalsSummary = implode(', ', $goalList);
            $p2 = "Las emociones no tardaron en manifestarse en el marcador gracias a las apariciones estelares de {$goalsSummary}. Cada anotación encendió a los aficionados en las gradas y en el Live Stream de la plataforma, que vivieron momentos de pura taquicardia futbolística ante transiciones a velocidad vertiginosa.";
        } else {
            $p2 = 'Las defensas de ambos cuadros se vistieron de héroes a lo largo de los 90 minutos. Con coberturas milimétricas y guardametas que ahogaron el grito de gol en la línea de sentencia, el candado jamás pudo ser vulnerado pese a los constantes intentos en ofensiva.';
        }

        // Párrafo 3: Disciplina y Clímax
        if ($redCards->isNotEmpty()) {
            $p3 = 'El encuentro no estuvo exento de fricción y pierna fuerte, obligando al cuerpo arbitral a recurrir a la cartulina roja para contener los ánimos. Con superioridad numérica en el tramo final, los espacios se multiplicaron y el partido entró en una fase de puro vértigo y tensión.';
        } else {
            $p3 = 'Bajo un arbitraje firme y ecuánime, el cotejo fluyó con lealtad y rigor deportivo. Ambos técnicos movieron sus banquillos en la etapa complementaria buscando un golpe de timón táctico que desequilibrara la balanza.';
        }

        // Párrafo 4: Desenlace y MVP
        if ($mvp) {
            $mvpName = $mvp->name;
            $mvpText = "La ovación unánime fue para {$mvpName}, coronado oficialmente como el MVP del partido tras liderar a su escuadra con jerarquía y recibir el respaldo masivo de los aficionados en la votación en tiempo real.";
        } else {
            $mvpText = "El colectivo de {$winner} terminó marcando la diferencia en los detalles que definen los campeonatos.";
        }

        $p4 = "Al sonar el pitazo final del colegiado, el electrónico selló el {$homeScore}-{$awayScore} definitivo. {$mvpText} Con este desenlace, la tabla de posiciones se comprime aún más, consolidando a SIGET como el epicentro de la emoción y el profesionalismo deportivo.";

        $body = "{$p1}\n\n{$p2}\n\n{$p3}\n\n{$p4}";

        return [
            'title' => $title,
            'body' => $body,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function callGeminiApi(MatchGame $match, string $apiKey): ?array
    {
        $prompt = $this->buildLlmPrompt($match);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(8)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.7,
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! $text) {
            return null;
        }

        $parsed = json_decode($text, true);

        return [
            'title' => $parsed['title'] ?? 'Crónica Oficial del Partido',
            'body' => $parsed['body'] ?? $text,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function callOpenAiApi(MatchGame $match, string $apiKey): ?array
    {
        $prompt = $this->buildLlmPrompt($match);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(8)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un narrador y cronista deportivo profesional de élite internacional. Responde estrictamente en formato JSON con las claves "title" y "body".'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;
        if (! $content) {
            return null;
        }

        $parsed = json_decode($content, true);

        return [
            'title' => $parsed['title'] ?? 'Crónica Oficial del Partido',
            'body' => $parsed['body'] ?? $content,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function buildLlmPrompt(MatchGame $match): string
    {
        $events = $match->events()->with('player')->get()->map(fn ($e) => [
            'minute' => $e->minute,
            'type' => $e->event_type,
            'player' => $e->player?->name,
            'team_id' => $e->team_id,
        ])->toArray();

        $payload = [
            'tournament' => $match->tournament?->name,
            'stage' => $match->stage,
            'venue' => $match->venue?->name,
            'home_team' => $match->homeTeam?->name,
            'away_team' => $match->awayTeam?->name,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'mvp' => $match->mvpPlayer?->name,
            'events' => $events,
        ];

        return 'Redacta una crónica periodística deportiva vibrante y profesional en español a partir del siguiente reporte de partido: '.json_encode($payload, JSON_UNESCAPED_UNICODE).'. Devuelve un JSON con {"title": "...", "body": "..."}. La crónica debe incluir 4 párrafos que narren el contexto, momentos clave, jugadas estelares y el impacto en el torneo.';
    }
}
