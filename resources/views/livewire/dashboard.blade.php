@php
    $icons = [
        'income'  => 'tabler-cash',
        'expense' => 'tabler-shopping-cart',
        'profit'  => 'tabler-chart-line',
        'orders'  => 'tabler-shopping-bag',
        'up'      => 'tabler-trending-up',
        'down'    => 'tabler-trending-down',
    ];
    $chart = $this->chart;
@endphp

<div @if($autoUpdating) wire:poll.5s="tick" @endif>

    <div class="page-head">
        <div>
            <h1>{{ __('Welcome back, :name', ['name' => \App\Models\User::first()?->name ?? __('there')]) }}</h1>
            <p>{{ __("Here's a clear overview of your store performance this week.") }}</p>
        </div>
    </div>

    <div class="content-grid">

        <!-- LEFT COLUMN -->
        <div class="col-left">

            <!-- METRICS -->
            <div class="metric-row">
                @foreach ($this->metrics as $m)
                    <div class="card metric">
                        <div class="metric-top">
                            <span>{{ $m['label'] }}</span>
                            <span class="metric-icon"><x-dynamic-component :component="$icons[$m['icon']]" /></span>
                        </div>
                        <p class="metric-value tabular">{{ $m['value'] }}</p>
                        <span class="metric-delta {{ $m['direction'] }}">
                            <x-dynamic-component :component="$icons[$m['direction']]" />
                            {{ $m['delta'] }} {{ __('vs last week') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- DAILY ORDERS CHART -->
            <div class="card chart-card">
                <div class="card-head">
                    <div>
                        <h2>{{ __('Daily orders') }}</h2>
                        <p>{{ __('Track order volume trends this week') }}</p>
                    </div>
                    <button class="icon-btn" wire:click="toggleAutoUpdate">
                        <x-tabler-refresh :class="$autoUpdating ? 'spin' : ''" />
                        {{ $autoUpdating ? __('Updating') : __('Auto update') }}
                    </button>
                </div>
                <div class="chart-wrap">
                    <svg viewBox="0 0 650 190" style="width:100%;display:block;overflow:visible;" role="img" aria-label="{{ __('Daily orders line chart, Monday through Sunday') }}">
                        <defs>
                            <linearGradient id="areaGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#c6f135" stop-opacity=".35"/>
                                <stop offset="100%" stop-color="#c6f135" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g class="chart-grid">
                            @foreach ($chart['yLabels'] as $i => $label)
                                @php $y = $chart['top'] + ($i * ($chart['bottom'] - $chart['top']) / 4); @endphp
                                <line x1="{{ $chart['left'] }}" y1="{{ $y }}" x2="{{ $chart['right'] }}" y2="{{ $y }}"/>
                            @endforeach
                        </g>
                        <g class="chart-axis-y">
                            @foreach ($chart['yLabels'] as $i => $label)
                                @php $y = $chart['top'] + ($i * ($chart['bottom'] - $chart['top']) / 4) + 3; @endphp
                                <text x="0" y="{{ $y }}">{{ $label }}</text>
                            @endforeach
                        </g>
                        <path fill="url(#areaGradient)" d="{{ $chart['area'] }}"/>
                        <path class="chart-line" d="{{ $chart['line'] }}"/>
                        <g>
                            @foreach ($chart['points'] as $p)
                                <circle class="chart-point" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5">
                                    <title>{{ $p['day'] }}: {{ __(':count orders', ['count' => $p['value']]) }}</title>
                                </circle>
                            @endforeach
                        </g>
                        <g class="chart-axis-x">
                            @foreach ($chart['points'] as $p)
                                <text x="{{ $p['x'] }}" y="188" text-anchor="middle">{{ $p['day'] }}</text>
                            @endforeach
                        </g>
                    </svg>
                    @if ($chart['peak'])
                        <p style="font-size:11px;color:var(--ink-soft);margin:6px 2px 0;">
                            {{ __('Peak day:') }} <strong style="color:var(--ink);">{{ $chart['peak']['day'] }}</strong> {{ __('with :count orders', ['count' => $chart['peak']['value']]) }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- BOTTOM ROW -->
            <div class="bottom-row">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h2>{{ __('Top selling products') }}</h2>
                            <p>{{ __('Last 30 days, by units sold') }}</p>
                        </div>
                    </div>
                    <div class="bars">
                        @forelse ($this->topProducts as $i => $p)
                            <div class="bar-row">
                                <span class="bar-label" title="{{ $p['label'] }}">{{ $p['label'] }}</span>
                                <div class="bar-track">
                                    <div class="bar-fill {{ $i % 2 ? 'alt' : '' }}" style="width:{{ round(($p['count'] / $p['max']) * 100) }}%;"></div>
                                </div>
                                <span class="bar-count tabular">{{ $p['count'] }}</span>
                            </div>
                        @empty
                            <p class="stock-empty">{{ __('No sales recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2>{{ __('Recent transactions') }}</h2>
                        <a href="{{ route('pos.terminal') }}" wire:navigate class="kebab" aria-label="{{ __('New sale') }}">
                            <x-tabler-plus />
                        </a>
                    </div>
                    <div class="tx-list">
                        @forelse ($this->recentTransactions as $t)
                            <div class="tx-row">
                                <div>
                                    <p class="tx-name">{{ $t->customer?->name ?? __('Walk-in customer') }}</p>
                                    <p class="tx-id">{{ $t->invoice_number }}</p>
                                </div>
                                <div>
                                    <p class="tx-amount tabular">Rp {{ number_format((float) $t->grand_total, 0, ',', '.') }}</p>
                                    <p class="tx-status {{ $t->payment_status }}">{{ \App\Support\Labels::paymentStatus($t->payment_status) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="stock-empty">{{ __('No transactions yet — start a sale from the Point of Sale page.') }}</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <aside class="rail">

            <div class="rail-actions">
                <button class="pill-btn">
                    <x-tabler-building-store />
                    {{ __('Main outlet') }}
                </button>
                <a href="{{ route('pos.terminal') }}" wire:navigate class="pill-btn primary">
                    <x-tabler-plus stroke-width="2.5" />
                    {{ __('New transaction') }}
                </a>
            </div>

            <div class="card stat-card">
                <div class="card-head" style="padding:0;">
                    <p style="font-size:13.5px;font-weight:600;">{{ __('Active cashiers') }}</p>
                </div>
                <div class="stat-body">
                    <div class="stat-icon">
                        <x-tabler-users style="color:#1c1f1a" />
                    </div>
                    <div>
                        <p class="stat-value">{{ __(':count staff', ['count' => $this->activeCashiers]) }}</p>
                        <p class="stat-caption">{{ __('Currently active') }}</p>
                    </div>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-head" style="padding:0;">
                    <p style="font-size:13.5px;font-weight:600;">{{ __('Total products') }}</p>
                </div>
                <div class="stat-body">
                    <div class="stat-icon">
                        <x-tabler-package style="color:#1c1f1a" />
                    </div>
                    <div>
                        <p class="stat-value">{{ __(':count items', ['count' => $this->totalProducts]) }}</p>
                        <p class="stat-caption">{{ __('Across all categories') }}</p>
                    </div>
                </div>
            </div>

            <div class="card pay-card">
                <div class="card-head" style="padding:0;">
                    <div>
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Payment methods') }}</p>
                        <p style="font-size:11px;color:var(--ink-soft);margin-top:2px;">{{ __('Last 30 days') }}</p>
                    </div>
                </div>
                @if ($this->paymentMethods->isEmpty())
                    <p class="stock-empty">{{ __('No payments recorded yet.') }}</p>
                @else
                    @php $payMax = max(1, $this->paymentMethods->max('count')); @endphp
                    <div class="pay-chart">
                        @foreach ($this->paymentMethods as $p)
                            <div class="pay-col">
                                <span class="pay-count tabular">{{ $p['count'] }}</span>
                                <div class="pay-bar" style="height:{{ round(($p['count'] / $payMax) * 100) }}%;background:{{ $p['color'] }};"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="pay-legend">
                        @foreach ($this->paymentMethods as $p)
                            <span><i style="background:{{ $p['color'] }};"></i>{{ $p['label'] }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card stock-card">
                <div class="stock-head">
                    <p style="font-size:13.5px;font-weight:600;">{{ __('Low stock alert') }}</p>
                    <x-tabler-alert-triangle />
                </div>
                @forelse ($this->lowStock as $s)
                    <div class="stock-row">
                        <span class="stock-name">{{ $s['name'] }}</span>
                        <span class="stock-qty low tabular">{{ $s['qty'] }}</span>
                    </div>
                @empty
                    <p class="stock-empty">{{ __('All products are well stocked.') }}</p>
                @endforelse
            </div>

        </aside>
    </div>
</div>
