<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Reports') }}</h1>
            <p>{{ __('A quick read on revenue, category mix and outstanding farmer credit.') }}</p>
        </div>
    </div>

    <div class="metric-row" style="grid-template-columns:repeat(2,1fr);">
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Revenue this month') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($this->monthlyRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Outstanding receivables') }}</span></div>
            <p class="metric-value tabular">Rp {{ number_format($this->outstandingReceivables, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bottom-row">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Revenue by category') }}</h2>
                <p>{{ __('All-time, from completed sale items') }}</p>
            </div>
            <div class="bars">
                @php $catMax = max(1, (float) ($this->salesByCategory->max('revenue') ?? 1)); @endphp
                @forelse ($this->salesByCategory as $c)
                    <div class="bar-row">
                        <span class="bar-label" title="{{ $c['name'] }}">{{ $c['name'] }}</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ round(($c['revenue'] / $catMax) * 100) }}%;"></div>
                        </div>
                        <span class="bar-count tabular">Rp {{ number_format($c['revenue'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="stock-empty">{{ __('No categories yet.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>{{ __('Top outstanding balances') }}</h2>
                <p>{{ __('Farmers currently carrying credit') }}</p>
            </div>
            <div class="tx-list">
                @forelse ($this->topDebtors as $customer)
                    <div class="tx-row">
                        <div>
                            <p class="tx-name">{{ $customer->name }}</p>
                            <p class="tx-id">{{ $customer->farmer_group ?? __('No group') }}</p>
                        </div>
                        <div>
                            <p class="tx-amount tabular">Rp {{ number_format((float) $customer->current_debt, 0, ',', '.') }}</p>
                            <p class="tx-status unpaid">{{ __('Owed') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="stock-empty">{{ __('No outstanding balances. 🎉') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
