<?php

use App\Support\Reicon;
use Illuminate\Support\HtmlString;

if (! function_exists('reicon')) {
    /**
     * Renderiza un icono SVG del repositorio oficial Reicon (https://github.com/dqev/reicon.git).
     */
    function reicon(string $name, string|array $attributes = '', string $variant = 'outline'): HtmlString
    {
        return Reicon::render($name, $attributes, $variant);
    }
}
