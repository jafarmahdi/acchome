<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('currency_symbol')) {
    function currency_symbol(?string $currency = null): string
    {
        $map = [
            'IQD' => 'د.ع',
            'USD' => '$',
            'EUR' => 'EUR ',
            'GBP' => 'GBP ',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
        ];

        if ($currency === null || $currency === '') {
            $currency = Auth::check()
                ? (Auth::user()->family->currency ?? config('app.currency', 'IQD'))
                : config('app.currency', 'IQD');
        }

        $normalized = strtoupper(trim((string) $currency));

        return $map[$normalized] ?? (string) $currency;
    }
}

if (!function_exists('currency_decimals')) {
    function currency_decimals(?string $currency = null): int
    {
        $currency = $currency ?: (Auth::check()
            ? (Auth::user()->family->currency ?? config('app.currency', 'IQD'))
            : config('app.currency', 'IQD'));

        return strtoupper((string) $currency) === 'IQD' ? 0 : 2;
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?: (Auth::check()
            ? (Auth::user()->family->currency ?? config('app.currency', 'IQD'))
            : config('app.currency', 'IQD'));

        return currency_symbol($currency) . number_format($amount, currency_decimals($currency));
    }
}

if (!function_exists('latest_exchange_rate')) {
    function latest_exchange_rate(?string $fromCurrency, ?string $toCurrency, ?int $familyId = null, $date = null): ?float
    {
        $fromCurrency = strtoupper(trim((string) $fromCurrency));
        $toCurrency = strtoupper(trim((string) $toCurrency));

        if ($fromCurrency === '' || $toCurrency === '') {
            return null;
        }

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $familyId = $familyId ?: current_family_id();
        if (!$familyId) {
            return null;
        }

        $date = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();

        $direct = \App\Models\ExchangeRate::query()
            ->where('family_id', $familyId)
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->value('rate');

        if ($direct) {
            return (float) $direct;
        }

        $reverse = \App\Models\ExchangeRate::query()
            ->where('family_id', $familyId)
            ->where('from_currency', $toCurrency)
            ->where('to_currency', $fromCurrency)
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->value('rate');

        if ($reverse && (float) $reverse != 0.0) {
            return 1 / (float) $reverse;
        }

        return null;
    }
}

if (!function_exists('convert_currency')) {
    function convert_currency(float $amount, ?string $fromCurrency, ?string $toCurrency, ?int $familyId = null, $date = null): ?float
    {
        $rate = latest_exchange_rate($fromCurrency, $toCurrency, $familyId, $date);

        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, currency_decimals($toCurrency));
    }
}

if (!function_exists('app_direction')) {
    function app_direction(): string
    {
        return session('direction', config('app.direction', 'ltr'));
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return app_direction() === 'rtl';
    }
}

if (!function_exists('current_family_id')) {
    function current_family_id(): ?int
    {
        return Auth::check() ? Auth::user()->family_id : null;
    }
}

if (!function_exists('format_date')) {
    function format_date($date, ?string $format = null): string
    {
        $format = $format ?? config('app.date_format', 'Y-m-d');
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('percentage_of')) {
    function percentage_of(float $part, float $total): float
    {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, 1);
    }
}
