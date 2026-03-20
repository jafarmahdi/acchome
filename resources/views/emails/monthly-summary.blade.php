<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #7C3AED, #6D28D9); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; }
        .grid { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .stat { flex: 1; min-width: 120px; background: #f8fafc; border-radius: 8px; padding: 16px; text-align: center; }
        .stat .label { font-size: 12px; color: #64748b; text-transform: uppercase; }
        .stat .value { font-size: 22px; font-weight: 700; margin-top: 4px; }
        h3 { color: #1e293b; margin-top: 24px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; color: #64748b; font-weight: 600; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .bar { height: 8px; border-radius: 4px; background: #e2e8f0; }
        .bar-fill { height: 8px; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📈 {{ __('Monthly Finance Report') }}</h1>
            <p style="margin:5px 0 0;opacity:0.9;font-size:14px;">{{ $family->name }} &middot; {{ $data['month'] ?? '' }}</p>
        </div>
        <div class="content">
            <div class="grid">
                <div class="stat">
                    <div class="label">{{ __('Income') }}</div>
                    <div class="value" style="color:#22c55e">{{ format_currency($data['income'] ?? 0) }}</div>
                </div>
                <div class="stat">
                    <div class="label">{{ __('Expenses') }}</div>
                    <div class="value" style="color:#ef4444">{{ format_currency($data['expenses'] ?? 0) }}</div>
                </div>
                <div class="stat">
                    <div class="label">{{ __('Balance') }}</div>
                    <div class="value" style="color:#3b82f6">{{ format_currency($data['balance'] ?? 0) }}</div>
                </div>
            </div>

            @if(!empty($data['category_breakdown']))
            <h3>{{ __('Spending by Category') }}</h3>
            @foreach($data['category_breakdown'] as $cat)
            <div style="margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                    <span style="color:#334155">{{ $cat['name'] }}</span>
                    <span style="color:#64748b">{{ format_currency($cat['total']) }} ({{ $cat['percent'] }}%)</span>
                </div>
                <div class="bar"><div class="bar-fill" style="width:{{ $cat['percent'] }}%;background:{{ $cat['color'] ?? '#3B82F6' }}"></div></div>
            </div>
            @endforeach
            @endif

            @if(!empty($data['accounts']))
            <h3>{{ __('Account Balances') }}</h3>
            <table>
                <tr><th>{{ __('Account') }}</th><th style="text-align:right">{{ __('Balance') }}</th></tr>
                @foreach($data['accounts'] as $acc)
                <tr>
                    <td>{{ $acc['name'] }}</td>
                    <td style="text-align:right;font-weight:600">{{ format_currency($acc['balance']) }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </div>
        <div class="footer">
            <p>{{ config('app.name') }} &middot; {{ __('Your household finance companion') }}</p>
        </div>
    </div>
</body>
</html>
