<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Purchase History') }}</h1>
            <p>{{ __('Summary of purchase invoices from suppliers.') }}</p>
        </div>
        <a href="{{ route('purchases.create') }}" wire:navigate class="btn btn-primary">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('New stock entry') }}
        </a>
    </div>

    @if (session('flash'))
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ session('flash') }}
        </div>
    @endif

    <div class="rail" style="flex-direction:row;flex-wrap:wrap;margin-bottom:16px;">
        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Invoices') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-file-invoice style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value">{{ __(':count invoice(s)', ['count' => $this->summary['count']]) }}</p>
                    <p class="stat-caption">{{ __('Matching current filters') }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Total purchased') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-truck-delivery style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value tabular">Rp {{ number_format($this->summary['total'], 0, ',', '.') }}</p>
                    <p class="stat-caption">{{ __('Goods received value') }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Paid to suppliers') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-cash style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value tabular">Rp {{ number_format($this->summary['paid'], 0, ',', '.') }}</p>
                    <p class="stat-caption">{{ __('Settled amount') }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Outstanding') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-alert-triangle style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value tabular">Rp {{ number_format($this->summary['outstanding'], 0, ',', '.') }}</p>
                    <p class="stat-caption">{{ __('Still owed to suppliers') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" class="input" placeholder="{{ __('Search purchase # or supplier…') }}" wire:model.live.debounce.300ms="search">
                    <select class="select" wire:model.live="supplierId">
                        <option value="">{{ __('All suppliers') }}</option>
                        @foreach ($this->suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <select class="select" wire:model.live="status">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="paid">{{ __('Paid') }}</option>
                        <option value="partial">{{ __('Partial') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                    </select>
                    <input type="date" class="input" wire:model.live="dateFrom" aria-label="{{ __('From date') }}">
                    <input type="date" class="input" wire:model.live="dateTo" aria-label="{{ __('To date') }}">
                    <button type="button" class="btn btn-sm" wire:click="resetFilters">{{ __('Reset') }}</button>
                </div>
                <span class="cell-muted">{{ __(':count invoice(s)', ['count' => $purchases->total()]) }}</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Purchase #') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Items') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Balance') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        @php $balance = max(0, (float) $purchase->total_amount - (float) $purchase->paid_amount); @endphp
                        <tr wire:key="purchase-{{ $purchase->id }}">
                            <td class="cell-strong">{{ $purchase->purchase_number }}</td>
                            <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                            <td class="cell-muted">{{ $purchase->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                            <td class="tabular">{{ $purchase->items_count }}</td>
                            <td class="tabular">Rp {{ number_format((float) $purchase->total_amount, 0, ',', '.') }}</td>
                            <td class="tabular">Rp {{ number_format((float) $purchase->paid_amount, 0, ',', '.') }}</td>
                            <td class="tabular">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $purchase->payment_status === 'paid' ? 'badge-good' : ($purchase->payment_status === 'partial' ? 'badge-warn' : 'badge-bad') }}">
                                    {{ \App\Support\Labels::paymentStatus($purchase->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="viewPurchase({{ $purchase->id }})" aria-label="{{ __('View details') }}">
                                        <x-tabler-eye />
                                    </button>
                                    <button class="kebab" wire:click="askDelete({{ $purchase->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><p class="table-empty">{{ __('No purchases match your filters yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $purchases->currentPage(), 'last' => $purchases->lastPage()]) }}</span>
            {{ $purchases->links() }}
        </div>
    </div>

    @if ($deletingId)
        <div class="modal-backdrop" wire:click.self="cancelDelete">
            <div class="modal-panel" style="max-width:440px;">
                <div class="modal-head">
                    <h3>{{ __('Delete :name?', ['name' => $deletingLabel]) }}</h3>
                    <button class="kebab" wire:click="cancelDelete" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <div class="modal-body">
                    <p style="color:var(--ink-soft);font-size:13px;">{{ __('The goods received on this purchase will be removed from stock. This action cannot be undone.') }}</p>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn" wire:click="cancelDelete">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteConfirmed" wire:loading.attr="disabled" wire:target="deleteConfirmed">
                        <span wire:loading.remove wire:target="deleteConfirmed">{{ __('Yes, delete') }}</span>
                        <span wire:loading wire:target="deleteConfirmed">{{ __('Deleting…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($this->viewingPurchase)
        @php $detail = $this->viewingPurchase; @endphp
        <div class="modal-backdrop" wire:click.self="closeDetail">
            <div class="modal-panel" style="max-width:760px;">
                <div class="modal-head">
                    <div>
                        <h3>{{ $detail->purchase_number }}</h3>
                        <p style="font-size:11.5px;color:var(--ink-soft);margin-top:2px;">
                            {{ $detail->supplier?->name ?? '—' }} &middot;
                            {{ $detail->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <button class="kebab" wire:click="closeDetail" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <div class="modal-body">
                    @if ($detail->note)
                        <p class="stat-caption" style="margin-bottom:12px;">
                            <strong>{{ __('Note') }}:</strong> {{ $detail->note }}
                        </p>
                    @endif

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Batch number') }}</th>
                                    <th>{{ __('Expires') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('Unit cost') }}</th>
                                    <th>{{ __('Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($detail->items as $item)
                                    <tr wire:key="item-{{ $item->id }}">
                                        <td class="cell-strong">{{ $item->product?->name ?? '—' }}</td>
                                        <td>{{ $item->batch?->batch_number ?? '—' }}</td>
                                        <td class="cell-muted">
                                            {{ $item->batch?->expiration_date?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '—' }}
                                        </td>
                                        <td class="tabular">{{ $item->quantity }}</td>
                                        <td class="tabular">Rp {{ number_format((float) $item->unit_cost, 0, ',', '.') }}</td>
                                        <td class="tabular">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><p class="table-empty">{{ __('No line items recorded for this invoice.') }}</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:14px;">
                        <div class="cart-summary-row">
                            <span>{{ __('Total') }}</span>
                            <span class="tabular">Rp {{ number_format((float) $detail->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>{{ __('Paid') }}</span>
                            <span class="tabular">Rp {{ number_format((float) $detail->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="cart-summary-row total">
                            <span>{{ __('Balance') }}</span>
                            <span class="tabular">Rp {{ number_format(max(0, (float) $detail->total_amount - (float) $detail->paid_amount), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn" wire:click="closeDetail">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
