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
                <p>{{ __('All-time, by sale items') }}</p>
            </div>
            <div class="rank-list">
                @forelse ($this->salesByCategory as $c)
                    <div class="rank-row">
                        <div class="rank-top">
                            <span class="rank-name" title="{{ $c['name'] }}">{{ $c['name'] }}</span>
                            <span class="rank-amount tabular">Rp {{ number_format($c['revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="rank-track" role="img" aria-label="{{ number_format($c['share'], 1) }}%">
                            <div class="rank-fill" style="width:{{ max($c['revenue'] > 0 ? 2 : 0, round($c['share'])) }}%;"></div>
                        </div>
                        <div class="rank-meta">
                            <span>{{ number_format($c['share'], 1) }}%</span>
                            <span>{{ __(':count unit(s) sold', ['count' => number_format($c['units'], 0, ',', '.')]) }}</span>
                        </div>
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
                    <p class="stock-empty">{{ __('No outstanding balances.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
