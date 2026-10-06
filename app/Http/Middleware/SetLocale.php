<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** Satu sumber whitelist, dipakai juga oleh route locale.switch. */
    public const SUPPORTED = ['en', 'id'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        // fallback_locale, karena setLocale() menimpa config('app.fallback_locale') di proses yang sama.

        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : config('app.fallback_locale'));

        return $next($request);
    }
}
