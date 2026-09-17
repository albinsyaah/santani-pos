<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Sales report') }}</h1>
            <p>{{ __('Revenue, gross profit and payment method breakdown.') }}</p>
        </div>
        <select class="select" style="max-width:200px;" wire:model.live="period">
            <option value="today">{{ __('Today') }}</option>
            <option value="week">{{ __('This week') }}</option>
            <option value="month">{{ __('This month') }}</option>
            <option value="year">{{ __('This year') }}</option>
            <option value="all">{{ __('All time') }}</option>
        </select>
    </div>

    <div class="metric-row">
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Revenue') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Gross profit') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Gross margin') }}</span></div>
            <p class="metric-value tabular">{{ number_format($grossMargin, 1) }}%</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Transactions') }}</span></div>
            <p class="metric-value tabular">{{ number_format($transactionCount) }}</p>
            <p style="font-size:11.5px;color:var(--ink-soft);margin:0;">{{ __('Avg. Rp :amount / sale', ['amount' => number_format($averageSale, 0, ',', '.')]) }}</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-head">
            <h2>{{ __('Revenue by payment method') }}</h2>
            <p>{{ __('For the selected period') }}</p>
        </div>
        <div class="bars">
            @php $methodMax = max(1, (float) ($byMethod->max('total') ?? 1)); @endphp
            @forelse ($byMethod as $m)
                <div class="bar-row">
                    <span class="bar-label" title="{{ $m['label'] }}">{{ $m['label'] }}</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:{{ round(($m['total'] / $methodMax) * 100) }}%;"></div>
                    </div>
                    <span class="bar-count tabular">Rp {{ number_format($m['total'], 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="stock-empty">{{ __('No sales in this period.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>{{ __('Transactions') }}</h2>
            <p>{{ __('Every sale in the selected period') }}</p>
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Payment method') }}</th>
                        <th>{{ __('Revenue') }}</th>
                        <th>{{ __('Profit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key="report-sale-{{ $sale->id }}">
                            <td class="cell-strong">{{ $sale->invoice_number }}</td>
                            <td class="cell-muted">{{ $sale->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? __('Walk-in customer') }}</td>
                            <td>{{ \App\Support\Labels::paymentMethod($sale->payment_method) }}</td>
                            <td class="tabular">Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</td>
                            <td class="tabular">Rp {{ number_format($this->saleProfit($sale), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><p class="table-empty">{{ __('No sales in this period.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $sales->currentPage(), 'last' => $sales->lastPage()]) }}</span>
            {{ $sales->links() }}
        </div>
    </div>
</div>
