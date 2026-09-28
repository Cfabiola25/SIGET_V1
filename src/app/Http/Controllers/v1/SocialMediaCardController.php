<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Services\v1\SocialMediaCardService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SocialMediaCardController extends Controller
{
    public function __construct(
        protected SocialMediaCardService $cardService
    ) {}

    /**
     * Muestra la interfaz interactiva para previsualizar y exportar assets para redes sociales.
     */
    public function preview(Request $request, MatchGame $match): View
    {
        $type = $request->query('type', 'final_score'); // 'final_score', 'lineup', 'mvp'
        $format = $request->query('format', 'feed'); // 'feed' (1:1) o 'story' (9:16)
        $teamId = $request->query('team_id') ? (int) $request->query('team_id') : null;

        $match->load(['homeTeam', 'awayTeam', 'tournament', 'venue', 'mvpPlayer.profile', 'lineups.player']);

        $svgContent = $this->cardService->renderSvg($match, $type, $format, $teamId);

        return view('v1.matches.social_card', [
            'match' => $match,
            'type' => $type,
            'format' => $format,
            'teamId' => $teamId,
            'svgContent' => $svgContent,
        ]);
    }

    /**
     * Descarga directa del asset renderizado (PNG con GD o SVG vectorial de alta fidelidad).
     */
    public function download(Request $request, MatchGame $match): Response
    {
        $type = $request->query('type', 'final_score');
        $format = $request->query('format', 'feed');
        $teamId = $request->query('team_id') ? (int) $request->query('team_id') : null;

        $match->load(['homeTeam', 'awayTeam', 'tournament', 'venue', 'mvpPlayer.profile', 'lineups.player']);

        $image = $this->cardService->renderImage($match, $type, $format, $teamId);

        $filename = "SIGET_{$type}_{$match->id}_{$format}.{$image['extension']}";

        return response($image['content'], 200, [
            'Content-Type' => $image['mime'],
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Devuelve el SVG puro para incrustar en componentes dinámicos.
     */
    public function rawSvg(Request $request, MatchGame $match): Response
    {
        $type = $request->query('type', 'final_score');
        $format = $request->query('format', 'feed');
        $teamId = $request->query('team_id') ? (int) $request->query('team_id') : null;

        $match->load(['homeTeam', 'awayTeam', 'tournament', 'venue', 'mvpPlayer.profile', 'lineups.player']);

        $svg = $this->cardService->renderSvg($match, $type, $format, $teamId);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
