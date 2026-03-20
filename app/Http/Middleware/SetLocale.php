<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supportedLocales = ['en', 'ar'];

    protected array $rtlLocales = ['ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        $direction = in_array($locale, $this->rtlLocales) ? 'rtl' : 'ltr';

        App::setLocale($locale);
        session(['locale' => $locale, 'direction' => $direction]);

        view()->share('locale', $locale);
        view()->share('direction', $direction);

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        // 1. Authenticated user preference
        if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
            if (in_array($locale, $this->supportedLocales)) {
                return $locale;
            }
        }

        // 2. Session
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
            if (in_array($locale, $this->supportedLocales)) {
                return $locale;
            }
        }

        // 3. Query parameter override
        if ($request->has('lang') && in_array($request->get('lang'), $this->supportedLocales)) {
            return $request->get('lang');
        }

        // 4. Browser preference
        $preferred = $request->getPreferredLanguage($this->supportedLocales);
        if ($preferred && in_array($preferred, $this->supportedLocales)) {
            return $preferred;
        }

        return config('app.locale', 'en');
    }
}
