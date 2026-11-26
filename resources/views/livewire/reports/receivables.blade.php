<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Farmer receivables report') }}</h1>
            <p>{{ __('Aging schedule for unpaid farmer balances.') }}</p>
        </div>
    </div>

    <div class="metric-row">
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Current') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($totals['current'], 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('1–30 days overdue') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($totals['b1_30'], 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('31–60 days overdue') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($totals['b31_60'], 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('61–90 days overdue') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($totals['b61_90'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card metric" style="margin-bottom:16px;max-width:320px;">
        <div class="metric-top"><span>{{ __('90+ days overdue') }}</span></div>
        <p class="metric-value tabular">Rp {{ number_format($totals['b90_plus'], 0, ',', '.') }}</p>
        <p style="font-size:11.5px;color:var(--bad);margin:0;font-weight:600;">{{ __('Highest collection risk') }}</p>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <input type="text" class="input" style="max-width:320px;" placeholder="{{ __('Search farmer name…') }}" wire:model.live.debounce.300ms="search">
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Farmer') }}</th>
                        <th>{{ __('Current') }}</th>
                        <th>{{ __('1–30 days') }}</th>
                        <th>{{ __('31–60 days') }}</th>
                        <th>{{ __('61–90 days') }}</th>
                        <th>{{ __('90+ days') }}</th>
                        <th>{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr wire:key="receivable-{{ $row['customer']->id }}">
                            <td class="cell-strong">{{ $row['customer']->name }}</td>
                            <td class="tabular">{{ $row['buckets']['current'] > 0 ? 'Rp '.number_format($row['buckets']['current'], 0, ',', '.') : '—' }}</td>
                            <td class="tabular">{{ $row['buckets']['b1_30'] > 0 ? 'Rp '.number_format($row['buckets']['b1_30'], 0, ',', '.') : '—' }}</td>
                            <td class="tabular">{{ $row['buckets']['b31_60'] > 0 ? 'Rp '.number_format($row['buckets']['b31_60'], 0, ',', '.') : '—' }}</td>
                            <td class="tabular">
                                @if ($row['buckets']['b61_90'] > 0)
                                    <span class="badge badge-warn">Rp {{ number_format($row['buckets']['b61_90'], 0, ',', '.') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="tabular">
                                @if ($row['buckets']['b90_plus'] > 0)
                                    <span class="badge badge-bad">Rp {{ number_format($row['buckets']['b90_plus'], 0, ',', '.') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="tabular cell-strong">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><p class="table-empty">{{ __('No outstanding balances.') }}</p></td></tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td class="cell-strong">{{ __('Total') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($totals['current'], 0, ',', '.') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($totals['b1_30'], 0, ',', '.') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($totals['b31_60'], 0, ',', '.') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($totals['b61_90'], 0, ',', '.') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($totals['b90_plus'], 0, ',', '.') }}</td>
                            <td class="tabular cell-strong">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
