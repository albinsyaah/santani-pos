<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Sales History') }}</h1>
            <p>{{ __('Every invoice ever issued, searchable and reprintable.') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" class="input" placeholder="{{ __('Search invoice # or customer…') }}" wire:model.live.debounce.300ms="search">
                    <select class="select" wire:model.live="statusFilter">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="paid">{{ __('Paid') }}</option>
                        <option value="partial">{{ __('Partial') }}</option>
                        <option value="unpaid">{{ __('Unpaid') }}</option>
                    </select>
                </div>
                <span class="cell-muted">{{ __(':count invoice(s)', ['count' => $sales->total()]) }}</span>
            </div>
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Payment method') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td class="cell-strong">{{ $sale->invoice_number }}</td>
                            <td class="cell-muted">{{ $sale->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? __('Walk-in customer') }}</td>
                            <td>{{ \App\Support\Labels::paymentMethod($sale->payment_method) }}</td>
                            <td class="tabular">Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $sale->payment_status === 'paid' ? 'badge-good' : ($sale->payment_status === 'partial' ? 'badge-warn' : 'badge-bad') }}">
                                    {{ \App\Support\Labels::paymentStatus($sale->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-sm">
                                    <x-tabler-printer />
                                    {{ __('Reprint') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><p class="table-empty">{{ __('No sales recorded yet.') }}</p></td></tr>
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
