<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Expired pesticides report') }}</h1>
            <p>{{ __('Agrochemical stock nearing or past its expiration date.') }}</p>
        </div>
    </div>

    <div class="metric-row" style="grid-template-columns:repeat(2,1fr);">
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Already expired') }}</span></div>
            <p class="metric-value tabular">{{ number_format($expiredCount) }} <span style="font-size:13px;color:var(--ink-soft);font-weight:500;">{{ __('batches') }}</span></p>
            <p style="font-size:11.5px;color:var(--bad);margin:0;font-weight:600;">Rp {{ number_format($expiredValue, 0, ',', '.') }} {{ __('at cost') }}</p>
        </div>
        <div class="card metric">
            <div class="metric-top"><span>{{ __('Expiring soon') }}</span></div>
            <p class="metric-value tabular">{{ number_format($soonCount) }} <span style="font-size:13px;color:var(--ink-soft);font-weight:500;">{{ __('batches') }}</span></p>
            <p style="font-size:11.5px;color:var(--warn);margin:0;font-weight:600;">Rp {{ number_format($soonValue, 0, ',', '.') }} {{ __('at cost') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" class="input" placeholder="{{ __('Search product name…') }}" wire:model.live.debounce.300ms="search">
                    <select class="select" wire:model.live="windowDays">
                        <option value="expired">{{ __('Already expired') }}</option>
                        <option value="30">{{ __('Expiring within 30 days') }}</option>
                        <option value="60">{{ __('Expiring within 60 days') }}</option>
                        <option value="90">{{ __('Expiring within 90 days') }}</option>
                    </select>
                    <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--ink-soft);font-weight:500;">
                        <input type="checkbox" wire:model.live="restrictedOnly">
                        {{ __('Restricted items only') }}
                    </label>
                </div>
                <span class="cell-muted">{{ __(':count item(s)', ['count' => $batches->total()]) }}</span>
            </div>
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Batch #') }}</th>
                        <th>{{ __('Expiration date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Value at cost') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php $days = $this->daysLeft($batch); @endphp
                        <tr wire:key="batch-{{ $batch->id }}">
                            <td class="cell-strong">
                                {{ $batch->product->name }}
                                @if ($batch->product->is_restricted)
                                    <span class="badge badge-neutral">{{ __('Restricted') }}</span>
                                @endif
                            </td>
                            <td>{{ $batch->product->category?->name ?? '—' }}</td>
                            <td>{{ $batch->batch_number }}</td>
                            <td>{{ $batch->expiration_date->format('d M Y') }}</td>
                            <td>
                                @if ($days < 0)
                                    <span class="badge badge-bad">{{ __('Expired :days days ago', ['days' => abs($days)]) }}</span>
                                @elseif ($days <= 7)
                                    <span class="badge badge-bad">{{ __(':days days left', ['days' => $days]) }}</span>
                                @elseif ($days <= 30)
                                    <span class="badge badge-warn">{{ __(':days days left', ['days' => $days]) }}</span>
                                @else
                                    <span class="badge badge-neutral">{{ __(':days days left', ['days' => $days]) }}</span>
                                @endif
                            </td>
                            <td class="tabular">{{ number_format($batch->stock_quantity) }} {{ $batch->product->unit }}</td>
                            <td class="tabular">Rp {{ number_format((float) $batch->stock_quantity * (float) ($batch->purchase_price ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><p class="table-empty">{{ __('No batches match this window.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $batches->currentPage(), 'last' => $batches->lastPage()]) }}</span>
            {{ $batches->links() }}
        </div>
    </div>
</div>
