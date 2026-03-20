<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $rates = ExchangeRate::where('family_id', $familyId)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $conversion = null;

        if ($request->filled(['amount', 'from_currency', 'to_currency'])) {
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'from_currency' => 'required|string|max:10',
                'to_currency' => 'required|string|max:10',
                'conversion_date' => 'nullable|date',
            ]);

            $rate = latest_exchange_rate(
                $request->from_currency,
                $request->to_currency,
                $familyId,
                $request->conversion_date
            );

            $conversion = [
                'amount' => (float) $request->amount,
                'from_currency' => strtoupper($request->from_currency),
                'to_currency' => strtoupper($request->to_currency),
                'rate' => $rate,
                'converted_amount' => $rate !== null
                    ? convert_currency((float) $request->amount, $request->from_currency, $request->to_currency, $familyId, $request->conversion_date)
                    : null,
                'date' => $request->conversion_date ?: now()->toDateString(),
            ];
        }

        return view('exchange-rates.index', compact('rates', 'conversion'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|max:10|different:to_currency',
            'to_currency' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['family_id'] = auth()->user()->family_id;
        $validated['created_by'] = auth()->id();
        $validated['from_currency'] = strtoupper($validated['from_currency']);
        $validated['to_currency'] = strtoupper($validated['to_currency']);

        $rate = ExchangeRate::create($validated);

        AuditLog::record('created', $rate, null, $rate->toArray(), 'Created exchange rate');

        return redirect()->route('exchange-rates.index')
            ->with('success', __('Exchange rate saved successfully.'));
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        abort_if($exchangeRate->family_id !== auth()->user()->family_id, 403);

        $oldValues = $exchangeRate->toArray();
        $exchangeRate->delete();

        AuditLog::record('deleted', $exchangeRate, $oldValues, null, 'Deleted exchange rate');

        return redirect()->route('exchange-rates.index')
            ->with('success', __('Exchange rate deleted successfully.'));
    }
}
