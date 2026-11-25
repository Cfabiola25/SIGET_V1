<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Redirige a $target solo si apunta a este mismo sitio; en otro caso usa $fallback.
     * Previene redirecciones abiertas (Open Redirect, CWE-601) con el parámetro redirect_to.
     */
    protected function redirectToInternalOr(?string $target, string $fallback): RedirectResponse
    {
        return redirect($this->isInternalUrl($target) ? $target : $fallback);
    }

    private function isInternalUrl(?string $target): bool
    {
        if (blank($target) || str_starts_with($target, '//') || str_contains($target, '\\')) {
            return false;
        }

        if (str_starts_with($target, '/')) {
            return true;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);

        return in_array(parse_url($target, PHP_URL_SCHEME), ['http', 'https'], true)
            && parse_url($target, PHP_URL_HOST) === $appHost;
    }
}
