<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $headerLocale = $request->header('X-Lang')
            ?? $request->header('X-Language')
            ?? $request->header('Lang');

        if (is_string($headerLocale) && $headerLocale !== '') {
            return $this->normalizeLocale($headerLocale);
        }

        return $this->normalizeLocale($request->header('Accept-Language', 'en'));
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim(explode(',', $locale)[0]));

        return str_starts_with($normalized, 'ar') ? 'ar' : 'en';
    }
}
