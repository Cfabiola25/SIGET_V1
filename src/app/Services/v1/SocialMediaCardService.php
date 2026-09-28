<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Team;

class SocialMediaCardService
{
    /**
     * Genera el SVG vector de alta definición listo para Instagram Feed (1:1) o Story (9:16).
     */
    public function renderSvg(MatchGame $match, string $type = 'final_score', string $format = 'feed', ?int $teamId = null): string
    {
        $isStory = $format === 'story';
        $width = 1080;
        $height = $isStory ? 1920 : 1080;

        return match ($type) {
            'lineup' => $this->buildLineupSvg($match, $width, $height, $teamId),
            'mvp' => $this->buildMvpSvg($match, $width, $height),
            default => $this->buildFinalScoreSvg($match, $width, $height),
        };
    }

    /**
     * Genera binario PNG si la extensión GD está disponible; si no, retorna el SVG con Content-Type adecuado.
     */
    public function renderImage(MatchGame $match, string $type = 'final_score', string $format = 'feed', ?int $teamId = null): array
    {
        $svg = $this->renderSvg($match, $type, $format, $teamId);

        if (extension_loaded('gd')) {
            $pngBinary = $this->renderPngWithGd($match, $type, $format, $teamId);
            if ($pngBinary) {
                return [
                    'mime' => 'image/png',
                    'content' => $pngBinary,
                    'extension' => 'png',
                ];
            }
        }

        return [
            'mime' => 'image/svg+xml',
            'content' => $svg,
            'extension' => 'svg',
        ];
    }

    /**
     * Renderizador nativo con GD para compatibilidad de descarga directa de imagen PNG.
     */
    protected function renderPngWithGd(MatchGame $match, string $type, string $format, ?int $teamId): ?string
    {
        $isStory = $format === 'story';
        $w = 1080;
        $h = $isStory ? 1920 : 1080;

        $im = @imagecreatetruecolor($w, $h);
        if (! $im) {
            return null;
        }

        // Fondo oscuro premium #0b0f19
        $bg = imagecolorallocate($im, 11, 15, 25);
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);

        // Acentos de color
        $emerald = imagecolorallocate($im, 16, 185, 129);
        $cyan = imagecolorallocate($im, 6, 182, 212);
        $gold = imagecolorallocate($im, 245, 158, 11);
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocate($im, 148, 163, 184);
        $cardBg = imagecolorallocate($im, 20, 27, 45);

        // Barra superior decorativa
        imagefilledrectangle($im, 0, 0, $w, 16, $emerald);

        // Tarjeta central translúcida
        $margin = 60;
        imagefilledrectangle($im, $margin, 120, $w - $margin, $h - 100, $cardBg);

        // Textos básicos con GD
        $homeName = mb_substr($match->homeTeam?->name ?? 'LOCAL', 0, 20);
        $awayName = mb_substr($match->awayTeam?->name ?? 'VISITANTE', 0, 20);
        $tournament = mb_substr($match->tournament?->name ?? 'TORNEO SIGET', 0, 30);

        imagestring($im, 5, ($w / 2) - (strlen($tournament) * 4), 60, strtoupper($tournament), $gold);
        imagestring($im, 5, ($w / 2) - 35, 150, 'MATCH CENTER', $cyan);

        if ($type === 'final_score') {
            $scoreText = "{$match->home_score}  -  {$match->away_score}";
            imagestring($im, 5, ($w / 2) - (strlen($scoreText) * 4), ($h / 2) - 40, $scoreText, $white);
            imagestring($im, 5, 120, ($h / 2) - 40, $homeName, $emerald);
            imagestring($im, 5, $w - 280, ($h / 2) - 40, $awayName, $cyan);
            imagestring($im, 5, ($w / 2) - 50, ($h / 2) + 40, 'FINALIZADO', $emerald);
        } elseif ($type === 'mvp') {
            $mvpName = $match->mvpPlayer?->name ?? 'Mejor Jugador';
            imagestring($im, 5, ($w / 2) - 60, ($h / 2) - 80, 'OFFICIAL MVP', $gold);
            imagestring($im, 5, ($w / 2) - (strlen($mvpName) * 4), ($h / 2), strtoupper($mvpName), $white);
            $ratingText = 'RATING: '.($match->mvpPlayer?->profile?->performance_rating ?? '9.5');
            imagestring($im, 5, ($w / 2) - (strlen($ratingText) * 4), ($h / 2) + 50, $ratingText, $emerald);
        } else {
            imagestring($im, 5, ($w / 2) - 50, ($h / 2) - 80, 'ALINEACION OFICIAL', $white);
            imagestring($im, 5, 120, ($h / 2) - 20, $homeName, $emerald);
            imagestring($im, 5, $w - 280, ($h / 2) - 20, $awayName, $cyan);
        }

        // Marca de agua SIGET
        imagestring($im, 4, ($w / 2) - 35, $h - 60, 'SIGET SAAS', $muted);

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    /**
     * Construye la tarjeta SVG de Marcador Final (Final Score).
     */
    protected function buildFinalScoreSvg(MatchGame $match, int $width, int $height): string
    {
        $home = htmlspecialchars($match->homeTeam?->name ?? 'Equipo Local');
        $away = htmlspecialchars($match->awayTeam?->name ?? 'Equipo Visitante');
        $tournament = htmlspecialchars($match->tournament?->name ?? 'TORNEO OFICIAL');
        $venue = htmlspecialchars($match->venue?->name ?? 'Cancha Principal');
        $date = $match->match_date?->format('d M Y - H:i') ?? 'En Vivo';

        $homeGoals = $match->events()->where('event_type', 'goal')->where('team_id', $match->home_team_id)->with('player')->get();
        $awayGoals = $match->events()->where('event_type', 'goal')->where('team_id', $match->away_team_id)->with('player')->get();

        $homeScorersText = $homeGoals->map(fn ($g) => htmlspecialchars(($g->player?->name ?? 'Gol')." {$g->minute}'"))->implode(' • ');
        $awayScorersText = $awayGoals->map(fn ($g) => htmlspecialchars(($g->player?->name ?? 'Gol')." {$g->minute}'"))->implode(' • ');

        $homeInitial = mb_substr($match->homeTeam?->name ?? 'L', 0, 2);
        $awayInitial = mb_substr($match->awayTeam?->name ?? 'V', 0, 2);

        $badgeY = $height > 1200 ? 540 : 360;
        $scoreY = $height > 1200 ? 630 : 430;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#050814"/>
      <stop offset="40%" stop-color="#0f172a"/>
      <stop offset="100%" stop-color="#020617"/>
    </linearGradient>
    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#fbbf24"/>
      <stop offset="100%" stop-color="#d97706"/>
    </linearGradient>
    <linearGradient id="cardGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#1e293b" stop-opacity="0.85"/>
      <stop offset="100%" stop-color="#0f172a" stop-opacity="0.95"/>
    </linearGradient>
    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="15" result="blur" />
      <feComposite in="SourceGraphic" in2="blur" operator="over" />
    </filter>
  </defs>

  <rect width="100%" height="100%" fill="url(#bgGrad)"/>

  <!-- Champions Star Dust Glow Circles -->
  <circle cx="200" cy="200" r="180" fill="#10b981" opacity="0.15" filter="url(#glow)"/>
  <circle cx="880" cy="800" r="220" fill="#06b6d4" opacity="0.15" filter="url(#glow)"/>

  <!-- Top Banner -->
  <rect x="0" y="0" width="{$width}" height="14" fill="url(#goldGrad)"/>

  <!-- Header -->
  <g text-anchor="middle" font-family="'Outfit', 'Inter', system-ui, sans-serif">
    <text x="540" y="90" font-size="24" font-weight="900" fill="#10b981" letter-spacing="4">SIGET MATCH ENGINE</text>
    <text x="540" y="140" font-size="34" font-weight="800" fill="#f8fafc" letter-spacing="1">{$tournament}</text>
    <text x="540" y="180" font-size="20" font-weight="500" fill="#94a3b8">{$venue} • {$date}</text>
  </g>

  <!-- Main Card Container -->
  <rect x="70" y="230" width="940" height="{$height} - 320" rx="32" fill="url(#cardGrad)" stroke="#334155" stroke-width="2"/>

  <!-- Status Pill -->
  <g>
    <rect x="440" y="270" width="200" height="44" rx="22" fill="#10b981" opacity="0.2"/>
    <rect x="440" y="270" width="200" height="44" rx="22" fill="none" stroke="#10b981" stroke-width="2"/>
    <text x="540" y="300" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="18" font-weight="800" fill="#34d399" letter-spacing="3">FINALIZADO</text>
  </g>

  <!-- Home Team Crest / Badge -->
  <circle cx="280" cy="{$badgeY}" r="90" fill="#0f172a" stroke="#10b981" stroke-width="4"/>
  <text x="280" y="{$badgeY} + 22" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="52" font-weight="900" fill="#34d399">{$homeInitial}</text>
  <text x="280" y="{$badgeY} + 140" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="32" font-weight="800" fill="#ffffff">{$home}</text>

  <!-- Score Board -->
  <text x="540" y="{$scoreY}" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="110" font-weight="900" fill="#ffffff" letter-spacing="12">
    <tspan fill="#34d399">{$match->home_score}</tspan> - <tspan fill="#38bdf8">{$match->away_score}</tspan>
  </text>

  <!-- Away Team Crest / Badge -->
  <circle cx="800" cy="{$badgeY}" r="90" fill="#0f172a" stroke="#06b6d4" stroke-width="4"/>
  <text x="800" y="{$badgeY} + 22" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="52" font-weight="900" fill="#38bdf8">{$awayInitial}</text>
  <text x="800" y="{$badgeY} + 140" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="32" font-weight="800" fill="#ffffff">{$away}</text>

  <!-- Scorers Details Section -->
  <g font-family="'Inter', sans-serif" font-size="20" fill="#cbd5e1">
    <text x="280" y="{$badgeY} + 190" text-anchor="middle" fill="#94a3b8">{$homeScorersText}</text>
    <text x="800" y="{$badgeY} + 190" text-anchor="middle" fill="#94a3b8">{$awayScorersText}</text>
  </g>

  <!-- Footer Watermark -->
  <g text-anchor="middle" font-family="'Outfit', sans-serif">
    <text x="540" y="{$height} - 45" font-size="18" font-weight="700" fill="#64748b" letter-spacing="6">POWERED BY SIGET • SOCIAL MEDIA ENGINE</text>
  </g>
</svg>
SVG;
    }

    /**
     * Construye la tarjeta SVG de MVP de Oro.
     */
    protected function buildMvpSvg(MatchGame $match, int $width, int $height): string
    {
        $mvp = $match->mvpPlayer;
        $playerName = htmlspecialchars($mvp?->name ?? 'JUGADOR DESTACADO');
        $teamName = htmlspecialchars($mvp?->team?->name ?? 'SIGET');
        $rating = number_format($mvp?->profile?->performance_rating ?? 9.5, 1);
        $tournament = htmlspecialchars($match->tournament?->name ?? 'TORNEO OFICIAL');
        $goals = $match->events()->where('player_id', $mvp?->id)->where('event_type', 'goal')->count();
        $votes = $match->mvpVotes()->where('player_id', $mvp?->id)->count();

        $initial = mb_substr($mvp?->name ?? 'MVP', 0, 2);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
  <defs>
    <linearGradient id="goldBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0a0e1a"/>
      <stop offset="50%" stop-color="#1e1808"/>
      <stop offset="100%" stop-color="#020617"/>
    </linearGradient>
    <linearGradient id="goldPlate" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#fde68a"/>
      <stop offset="50%" stop-color="#d97706"/>
      <stop offset="100%" stop-color="#78350f"/>
    </linearGradient>
    <filter id="goldGlow">
      <feGaussianBlur stdDeviation="20" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <rect width="100%" height="100%" fill="url(#goldBg)"/>
  <circle cx="540" cy="500" r="320" fill="#f59e0b" opacity="0.1" filter="url(#goldGlow)"/>

  <!-- Top Badge -->
  <rect x="0" y="0" width="{$width}" height="16" fill="url(#goldPlate)"/>

  <g text-anchor="middle" font-family="'Outfit', sans-serif">
    <text x="540" y="90" font-size="24" font-weight="900" fill="#fbbf24" letter-spacing="4">SIGET MVP OF THE MATCH</text>
    <text x="540" y="140" font-size="30" font-weight="700" fill="#94a3b8">{$tournament}</text>
  </g>

  <!-- Player Golden Shield -->
  <g transform="translate(340, 210)">
    <polygon points="200,0 380,80 380,360 200,480 20,360 20,80" fill="#111827" stroke="url(#goldPlate)" stroke-width="6"/>
    <circle cx="200" cy="200" r="110" fill="#1f2937" stroke="#fbbf24" stroke-width="3"/>
    <text x="200" y="235" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="80" font-weight="900" fill="#fef08a">{$initial}</text>
  </g>

  <!-- Player Name & Team -->
  <g text-anchor="middle" font-family="'Outfit', sans-serif">
    <text x="540" y="780" font-size="52" font-weight="900" fill="#ffffff" letter-spacing="1">{$playerName}</text>
    <text x="540" y="835" font-size="32" font-weight="700" fill="#fbbf24">{$teamName}</text>
  </g>

  <!-- Stat Badges -->
  <g transform="translate(190, 890)" font-family="'Outfit', sans-serif">
    <!-- Stat 1: Rating -->
    <rect x="0" y="0" width="200" height="90" rx="16" fill="#1f2937" stroke="#374151" stroke-width="2"/>
    <text x="100" y="42" text-anchor="middle" font-size="34" font-weight="900" fill="#34d399">{$rating}</text>
    <text x="100" y="74" text-anchor="middle" font-size="16" font-weight="600" fill="#9ca3af">CALIFICACIÓN</text>

    <!-- Stat 2: Goles -->
    <rect x="250" y="0" width="200" height="90" rx="16" fill="#1f2937" stroke="#374151" stroke-width="2"/>
    <text x="350" y="42" text-anchor="middle" font-size="34" font-weight="900" fill="#38bdf8">{$goals}</text>
    <text x="350" y="74" text-anchor="middle" font-size="16" font-weight="600" fill="#9ca3af">GOLES EN PARTIDO</text>

    <!-- Stat 3: Votos Fans -->
    <rect x="500" y="0" width="200" height="90" rx="16" fill="#1f2937" stroke="#374151" stroke-width="2"/>
    <text x="600" y="42" text-anchor="middle" font-size="34" font-weight="900" fill="#fbbf24">{$votes}</text>
    <text x="600" y="74" text-anchor="middle" font-size="16" font-weight="600" fill="#9ca3af">VOTOS FANÁTICOS</text>
  </g>

  <!-- Watermark -->
  <text x="540" y="{$height} - 45" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="18" font-weight="700" fill="#64748b" letter-spacing="6">POWERED BY SIGET • OFFICIAL MVP AWARDS</text>
</svg>
SVG;
    }

    /**
     * Construye la tarjeta SVG de Alineación Táctica (Lineup).
     */
    protected function buildLineupSvg(MatchGame $match, int $width, int $height, ?int $teamId): string
    {
        $targetTeamId = $teamId ?: $match->home_team_id;
        $team = Team::find($targetTeamId);
        $teamName = htmlspecialchars($team?->name ?? 'Equipo');
        $opponent = htmlspecialchars($targetTeamId === $match->home_team_id ? ($match->awayTeam?->name ?? 'Rival') : ($match->homeTeam?->name ?? 'Rival'));
        $tournament = htmlspecialchars($match->tournament?->name ?? 'TORNEO SIGET');
        $date = $match->match_date?->format('d/m/Y - H:i') ?? 'Hoy';
        $venue = htmlspecialchars($match->venue?->name ?? 'Cancha');

        $starters = $match->lineups()->where('team_id', $targetTeamId)->where('is_starter', true)->with('player')->get();
        $substitutes = $match->lineups()->where('team_id', $targetTeamId)->where('is_starter', false)->with('player')->get();

        $startersSvg = '';
        $y = 350;
        foreach ($starters as $index => $lineup) {
            $num = $lineup->jersey_number ?? ($index + 1);
            $pName = htmlspecialchars($lineup->player?->name ?? "Jugador #{$num}");
            $pos = strtoupper(substr($lineup->position ?? 'MC', 0, 3));
            $startersSvg .= <<<SVG
    <g transform="translate(100, {$y})">
      <circle cx="30" cy="15" r="22" fill="#10b981" />
      <text x="30" y="22" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="18" font-weight="900" fill="#ffffff">{$num}</text>
      <text x="75" y="22" font-family="'Inter', sans-serif" font-size="22" font-weight="700" fill="#ffffff">{$pName}</text>
      <rect x="380" y="2" width="60" height="26" rx="6" fill="#1e293b" />
      <text x="410" y="20" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="14" font-weight="700" fill="#94a3b8">{$pos}</text>
    </g>
SVG;
            $y += 50;
        }

        $subsSvg = '';
        $subY = 350;
        foreach ($substitutes as $index => $lineup) {
            $num = $lineup->jersey_number ?? ($index + 12);
            $pName = htmlspecialchars($lineup->player?->name ?? "Jugador #{$num}");
            $subsSvg .= <<<SVG
    <g transform="translate(600, {$subY})">
      <circle cx="25" cy="15" r="18" fill="#334155" />
      <text x="25" y="21" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="15" font-weight="800" fill="#e2e8f0">{$num}</text>
      <text x="60" y="22" font-family="'Inter', sans-serif" font-size="20" font-weight="600" fill="#cbd5e1">{$pName}</text>
    </g>
SVG;
            $subY += 45;
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
  <rect width="100%" height="100%" fill="#090d1a"/>
  <rect x="0" y="0" width="{$width}" height="14" fill="#10b981"/>

  <g text-anchor="middle" font-family="'Outfit', sans-serif">
    <text x="540" y="80" font-size="22" font-weight="900" fill="#10b981" letter-spacing="4">ALINEACIÓN CONFIRMADA</text>
    <text x="540" y="130" font-size="44" font-weight="900" fill="#ffffff">{$teamName}</text>
    <text x="540" y="170" font-size="22" font-weight="600" fill="#94a3b8">VS {$opponent} • {$tournament}</text>
    <text x="540" y="200" font-size="18" font-weight="500" fill="#64748b">{$venue} | {$date}</text>
  </g>

  <!-- Titles for Starters & Subs -->
  <text x="100" y="290" font-family="'Outfit', sans-serif" font-size="26" font-weight="800" fill="#34d399">TITULARES (XI INICIAL)</text>
  <text x="600" y="290" font-family="'Outfit', sans-serif" font-size="26" font-weight="800" fill="#94a3b8">SUPLENTES</text>

  <!-- Starters Column -->
  {$startersSvg}

  <!-- Substitutes Column -->
  {$subsSvg}

  <text x="540" y="{$height} - 45" text-anchor="middle" font-family="'Outfit', sans-serif" font-size="18" font-weight="700" fill="#64748b" letter-spacing="6">POWERED BY SIGET • OFFICIAL LINEUP ENGINE</text>
</svg>
SVG;
    }
}
