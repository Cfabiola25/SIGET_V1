<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class Reicon
{
    protected static ?array $index = null;
    protected static array $categories = [];
    protected static array $iconCache = [];

    /**
     * Mapeo de sinónimos y alias amigables a nombres de Reicon.
     */
    protected static array $aliases = [
        'soccer' => 'football',
        'futbol' => 'football',
        'fútbol' => 'football',
        'balon' => 'football',
        'pelota' => 'football',
        'trofeo' => 'trophy',
        'copa' => 'trophy',
        'estrella' => 'star',
        'reloj' => 'clock',
        'cronometro' => 'clock',
        'tiempo' => 'clock',
        'calendario' => 'calendar',
        'escudo' => 'shield-check',
        'alerta' => 'alert-triangle',
        'advertencia' => 'alert-triangle',
        'tarjeta' => 'card',
        'tarjeta-amarilla' => 'card',
        'tarjeta-roja' => 'card',
        'cambio' => 'refresh',
        'sustitucion' => 'refresh',
        'noticias' => 'file-text',
        'cronica' => 'file-text',
        'ia' => 'cpu-bolt',
        'robot' => 'cpu-bolt',
        'goleador' => 'award',
        'mvp' => 'crown',
        'usuario' => 'user',
        'jugador' => 'user',
        'arbitro' => 'flag',
        'silbato' => 'flag',
        'sede' => 'pin',
        'cancha' => 'pin',
        'estadio' => 'pin',
        'buscar' => 'search',
        'lupa' => 'search',
        'bloqueado' => 'lock',
        'candado' => 'lock',
        'correo' => 'envelope',
        'email' => 'envelope',
        'telefono' => 'mobile',
        'celular' => 'mobile',
        'check-mark' => 'check',
        'ok' => 'check',
    ];

    /**
     * Obtiene el código SVG interno del icono Reicon.
     */
    public static function get(string $name, string $variant = 'outline'): ?string
    {
        $resolvedName = static::resolveName($name);
        $cacheKey = "{$resolvedName}:{$variant}";

        if (isset(static::$iconCache[$cacheKey])) {
            return static::$iconCache[$cacheKey];
        }

        static::loadIndex();

        if (! isset(static::$index[$resolvedName])) {
            return null;
        }

        $category = static::$index[$resolvedName];
        static::loadCategory($category);

        $icon = static::$categories[$category][$resolvedName] ?? null;
        if (! $icon) {
            return null;
        }

        $code = ($variant === 'filled' || $variant === 'f')
            ? ($icon['f'] ?: $icon['o'])
            : ($icon['o'] ?: $icon['f']);

        static::$iconCache[$cacheKey] = $code;

        return $code;
    }

    /**
     * Renderiza el elemento <svg> completo de Reicon.
     */
    public static function render(string $name, string|array $attributes = '', string $variant = 'outline'): HtmlString
    {
        $code = static::get($name, $variant);

        if (! $code) {
            // Si no se encuentra, renderizar un placeholder vacío seguro
            return new HtmlString('');
        }

        $attrString = is_array($attributes)
            ? static::buildAttributes($attributes)
            : 'class="reicon inline-block ' . trim($attributes) . '"';

        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' . $attrString . '>' . $code . '</svg>');
    }

    /**
     * Resuelve el nombre real en Reicon a través de alias.
     */
    public static function resolveName(string $name): string
    {
        $normalized = strtolower(trim($name));
        return static::$aliases[$normalized] ?? $normalized;
    }

    /**
     * Verifica si un icono existe en Reicon.
     */
    public static function exists(string $name): bool
    {
        $resolvedName = static::resolveName($name);
        static::loadIndex();

        return isset(static::$index[$resolvedName]);
    }

    /**
     * Carga el índice global name => category.
     */
    protected static function loadIndex(): void
    {
        if (static::$index !== null) {
            return;
        }

        $indexPath = resource_path('icons/reicon/index.json');
        if (file_exists($indexPath)) {
            static::$index = json_decode(file_get_contents($indexPath), true) ?: [];
        } else {
            static::$index = [];
        }
    }

    /**
     * Carga un archivo de categoría individual bajo demanda.
     */
    protected static function loadCategory(string $category): void
    {
        if (isset(static::$categories[$category])) {
            return;
        }

        $catPath = resource_path("icons/reicon/categories/{$category}.json");
        if (file_exists($catPath)) {
            static::$categories[$category] = json_decode(file_get_contents($catPath), true) ?: [];
        } else {
            static::$categories[$category] = [];
        }
    }

    /**
     * Construye string de atributos HTML.
     */
    protected static function buildAttributes(array $attributes): string
    {
        if (! isset($attributes['class'])) {
            $attributes['class'] = 'reicon inline-block size-5';
        } else {
            $attributes['class'] = 'reicon inline-block ' . $attributes['class'];
        }

        $html = [];
        foreach ($attributes as $key => $val) {
            $html[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string) $val, ENT_QUOTES));
        }

        return implode(' ', $html);
    }
}
