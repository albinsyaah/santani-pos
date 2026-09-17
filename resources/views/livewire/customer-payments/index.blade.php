<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Receivables payments') }}</h1>
            <p>{{ __('Record full or installment payments made by farmers against their balance.') }}</p>
        </div>
        <button class="btn btn-primary" wire:click="openCreateModal">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('Record payment') }}
        </button>
    </div>

    @if ($flash)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $flash }}
        </div>
    @endif

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <input type="text" class="input" style="max-width:320px;" placeholder="{{ __('Search farmer, reference…') }}" wire:model.live.debounce.300ms="search">
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Farmer / customer') }}</th>
                        <th>{{ __('Sale invoice') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="cell-strong">{{ $payment->customer->name ?? '—' }}</td>
                            <td>
                                @if ($payment->sale)
                                    {{ $payment->sale->invoice_number }}
                                @else
                                    <span class="badge badge-neutral">{{ __('General payment') }}</span>
                                @endif
                            </td>
                            <td class="tabular">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $payment->payment_method === 'cash' ? 'badge-lime' : 'badge-neutral' }}">
                                    {{ $payment->payment_method === 'cash' ? __('Cash') : __('Bank transfer') }}
                                </span>
                            </td>
                            <td>{{ $payment->reference_number ?? '—' }}</td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="askDelete({{ $payment->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><p class="table-empty">{{ __('No payments recorded yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $payments->currentPage(), 'last' => $payments->lastPage()]) }}</span>
            {{ $payments->links() }}
        </div>
    </div>

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel">
                <div class="modal-head">
                    <h3>{{ __('Record payment') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Farmer / customer') }}</label>
                            <select class="select @error('customerId') invalid @enderror" wire:model.live="customerId">
                                <option value="">{{ __('Select a farmer') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} @if($customer->current_debt > 0) — {{ __('owes Rp :amount', ['amount' => number_format((float) $customer->current_debt, 0, ',', '.')]) }} @endif</option>
                                @endforeach
                            </select>
                            @error('customerId') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        @if ($customerId)
                            <div class="field">
                                <label>{{ __('Apply to sale') }} <span class="hint">({{ __('optional') }})</span></label>
                                <select class="select" wire:model="saleId">
                                    <option value="">{{ __('General payment (not tied to a sale)') }}</option>
                                    @foreach ($openSales as $sale)
                                        <option value="{{ $sale->id }}">
                                            {{ $sale->invoice_number }} — {{ __('unpaid Rp :amount', ['amount' => number_format($sale->unpaid_balance, 0, ',', '.')]) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Amount paid (Rp)') }}</label>
                                <input type="number" step="0.01" min="0.01" class="input @error('amount') invalid @enderror" wire:model="amount">
                                @error('amount') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Payment date') }}</label>
                                <input type="date" class="input @error('paymentDate') invalid @enderror" wire:model="paymentDate">
                                @error('paymentDate') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Payment method') }}</label>
                                <select class="select" wire:model="paymentMethod">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="transfer">{{ __('Bank transfer') }}</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __('Reference number') }} <span class="hint">({{ __('optional') }})</span></label>
                                <input type="text" class="input" wire:model="referenceNumber">
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Notes') }} <span class="hint">({{ __('optional') }})</span></label>
                            <textarea class="textarea" wire:model="notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Save payment') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
