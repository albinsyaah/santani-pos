<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} {{ $sale->invoice_number }} · {{ config('app.name') }}</title>
    <style>
        :root{ --ink:#1c1f1a; --ink-soft:#565b52; --line:#e4e7de; }
        *{ box-sizing:border-box; }
        body{ font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace; color:var(--ink); background:#eef1e8; margin:0; padding:32px 16px; }
        .receipt{ max-width:380px; margin:0 auto; background:#fff; border:1px solid var(--line); border-radius:12px; padding:24px; }
        .center{ text-align:center; }
        .store-name{ font-size:16px; font-weight:700; letter-spacing:.02em; }
        .muted{ color:var(--ink-soft); font-size:11px; }
        hr{ border:none; border-top:1px dashed var(--line); margin:14px 0; }
        table{ width:100%; border-collapse:collapse; font-size:11.5px; }
        td{ padding:3px 0; vertical-align:top; }
        .row{ display:flex; justify-content:space-between; font-size:11.5px; padding:2px 0; }
        .row.total{ font-size:14px; font-weight:700; border-top:1px dashed var(--line); margin-top:6px; padding-top:8px; }
        .item-name{ font-weight:600; }
        .item-meta{ color:var(--ink-soft); font-size:10.5px; }
        .actions{ max-width:380px; margin:16px auto 0; display:flex; gap:8px; }
        .btn{ flex:1; display:inline-flex; align-items:center; justify-content:center; height:38px; border-radius:9px; border:1px solid var(--line); background:#fff; font-family: ui-sans-serif, system-ui, sans-serif; font-size:13px; font-weight:600; color:var(--ink); text-decoration:none; cursor:pointer; }
        .btn-primary{ background:#c6f135; border-color:#a9d928; }
        @media print{
            body{ background:#fff; padding:0; }
            .receipt{ border:none; border-radius:0; max-width:100%; }
            .actions{ display:none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <p class="store-name">{{ config('app.name') }}</p>
            <p class="muted">{{ __('Fertilizer & agrochemical point of sale') }}</p>
        </div>

        <hr>

        <div class="row"><span>{{ __('Invoice #') }}</span><span>{{ $sale->invoice_number }}</span></div>
        <div class="row"><span>{{ __('Date') }}</span><span>{{ $sale->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</span></div>
        <div class="row"><span>{{ __('Cashier') }}</span><span>{{ $sale->user?->name ?? '—' }}</span></div>
        <div class="row"><span>{{ __('Customer') }}</span><span>{{ $sale->customer?->name ?? __('Walk-in customer') }}</span></div>

        <hr>

        <table>
            @foreach ($sale->items as $item)
                <tr>
                    <td>
                        <p class="item-name">{{ $item->product?->name ?? __('Unknown product') }}</p>
                        <p class="item-meta">{{ $item->quantity }} &times; Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>

        <hr>

        <div class="row"><span>{{ __('Subtotal') }}</span><span>Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</span></div>
        @if ($sale->discount > 0)
            <div class="row"><span>{{ __('Discount') }}</span><span>-Rp {{ number_format((float) $sale->discount, 0, ',', '.') }}</span></div>
        @endif
        @if ($sale->tax > 0)
            <div class="row"><span>{{ __('Tax') }}</span><span>Rp {{ number_format((float) $sale->tax, 0, ',', '.') }}</span></div>
        @endif
        <div class="row total"><span>{{ __('Total') }}</span><span>Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</span></div>

        <hr>

        <div class="row"><span>{{ __('Payment method') }}</span><span>{{ \App\Support\Labels::paymentMethod($sale->payment_method) }}</span></div>
        <div class="row"><span>{{ __('Paid') }}</span><span>Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</span></div>
        @if ($sale->change_amount > 0)
            <div class="row"><span>{{ __('Change') }}</span><span>Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</span></div>
        @endif
        <div class="row"><span>{{ __('Status') }}</span><span>{{ \App\Support\Labels::paymentStatus($sale->payment_status) }}</span></div>

        <hr>

        <p class="center muted">{{ __('Thank you for your purchase!') }}</p>
    </div>

    <div class="actions">
        <a href="{{ route('sales.index') }}" class="btn">{{ __('Back to Sales History') }}</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">{{ __('Print') }}</button>
    </div>
</body>
</html>
