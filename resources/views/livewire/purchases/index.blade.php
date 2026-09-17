<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Purchases') }}</h1>
            <p>{{ __('Stock received from suppliers.') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <input type="text" class="input" style="max-width:320px;" placeholder="{{ __('Search purchase # or supplier…') }}" wire:model.live.debounce.300ms="search">
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Purchase #') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr wire:key="purchase-{{ $purchase->id }}">
                            <td class="cell-strong">{{ $purchase->purchase_number }}</td>
                            <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                            <td class="cell-muted">{{ $purchase->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                            <td class="tabular">Rp {{ number_format((float) $purchase->total_amount, 0, ',', '.') }}</td>
                            <td class="tabular">Rp {{ number_format((float) $purchase->paid_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $purchase->payment_status === 'paid' ? 'badge-good' : ($purchase->payment_status === 'partial' ? 'badge-warn' : 'badge-bad') }}">
                                    {{ \App\Support\Labels::paymentStatus($purchase->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><p class="table-empty">{{ __('No purchases recorded yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $purchases->currentPage(), 'last' => $purchases->lastPage()]) }}</span>
            {{ $purchases->links() }}
        </div>
    </div>
</div>
