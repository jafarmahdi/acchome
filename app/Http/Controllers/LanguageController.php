<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['en', 'ar'], true), 404);

        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        session([
            'locale' => $locale,
            'direction' => $direction,
        ]);

        if (Auth::check()) {
            Auth::user()->update([
                'locale' => $locale,
                'direction' => $direction,
            ]);
        }

        return back();
    }
}
