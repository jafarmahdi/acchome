<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #3B82F6, #2563EB); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; }
        .summary-card { background: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .label { color: #64748b; font-size: 14px; }
        .value { font-size: 20px; font-weight: 700; }
        .income { color: #22c55e; }
        .expense { color: #ef4444; }
        .savings { color: #3b82f6; }
        h3 { color: #1e293b; margin-top: 24px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; color: #64748b; font-weight: 600; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .footer { text-align: center; padding: 20px; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 {{ __('Weekly Finance Summary') }}</h1>
            <p style="margin:5px 0 0;opacity:0.9;font-size:14px;">{{ $family->name }} &middot; {{ $data['period'] ?? '' }}</p>
        </div>
        <div class="content">
            <div class="summary-card">
                <span class="label">{{ __('Income') }}</span>
                <span class="value income">{{ format_currency($data['income'] ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">{{ __('Expenses') }}</span>
                <span class="value expense">{{ format_currency($data['expenses'] ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">{{ __('Net Savings') }}</span>
                <span class="value savings">{{ format_currency(($data['income'] ?? 0) - ($data['expenses'] ?? 0)) }}</span>
            </div>

            @if(!empty($data['top_expenses']))
            <h3>{{ __('Top Expenses') }}</h3>
            <table>
                <tr><th>{{ __('Description') }}</th><th>{{ __('Category') }}</th><th style="text-align:right">{{ __('Amount') }}</th></tr>
                @foreach($data['top_expenses'] as $exp)
                <tr>
                    <td>{{ $exp['description'] }}</td>
                    <td>{{ $exp['category'] }}</td>
                    <td style="text-align:right;color:#ef4444;font-weight:600">{{ format_currency($exp['amount']) }}</td>
                </tr>
                @endforeach
            </table>
            @endif

            @if(!empty($data['budget_alerts']))
            <h3>⚠️ {{ __('Budget Alerts') }}</h3>
            @foreach($data['budget_alerts'] as $alert)
            <div style="background:#fef2f2;border-radius:6px;padding:10px;margin-bottom:8px;font-size:13px;color:#b91c1c;">
                <strong>{{ $alert['name'] }}</strong>: {{ $alert['percent'] }}% {{ __('used') }}
                ({{ format_currency($alert['spent']) }} / {{ format_currency($alert['amount']) }})
            </div>
            @endforeach
            @endif
        </div>
        <div class="footer">
            <p>{{ config('app.name') }} &middot; {{ __('Manage your finances smartly') }}</p>
        </div>
    </div>
</body>
</html>
