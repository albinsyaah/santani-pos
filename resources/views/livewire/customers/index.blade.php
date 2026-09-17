<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Farmers & customers') }}</h1>
            <p>{{ __('Manage credit limits and outstanding balances.') }}</p>
        </div>
        <button class="btn btn-primary" wire:click="openCreateModal">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('Add customer') }}
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
            <input type="text" class="input" style="max-width:320px;" placeholder="{{ __('Search name, phone, farmer group…') }}" wire:model.live.debounce.300ms="search">
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Farmer group') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Credit limit') }}</th>
                        <th>{{ __('Current debt') }}</th>
                        <th>{{ __('Available credit') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td class="cell-strong">{{ $customer->name }}</td>
                            <td>{{ $customer->farmer_group ?? '—' }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td class="tabular">Rp {{ number_format((float) $customer->credit_limit, 0, ',', '.') }}</td>
                            <td class="tabular">Rp {{ number_format((float) $customer->current_debt, 0, ',', '.') }}</td>
                            <td class="tabular">
                                Rp {{ number_format($customer->available_credit, 0, ',', '.') }}
                                @if ($customer->current_debt > 0)
                                    <span class="badge badge-warn">{{ __('Owes') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="openEditModal({{ $customer->id }})" aria-label="{{ __('Edit') }}">
                                        <x-tabler-edit />
                                    </button>
                                    <button class="kebab" wire:click="askDelete({{ $customer->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><p class="table-empty">{{ __('No customers yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $customers->currentPage(), 'last' => $customers->lastPage()]) }}</span>
            {{ $customers->links() }}
        </div>
    </div>

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel">
                <div class="modal-head">
                    <h3>{{ $editingId ? __('Edit customer') : __('Add customer') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Full name') }}</label>
                            <input type="text" class="input @error('name') invalid @enderror" wire:model="name">
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Phone') }}</label>
                                <input type="text" class="input" wire:model="phone">
                            </div>
                            <div class="field">
                                <label>{{ __('Farmer group') }} <span class="hint">({{ __('optional') }})</span></label>
                                <input type="text" class="input" wire:model="farmerGroup">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Address') }}</label>
                            <textarea class="textarea" wire:model="address"></textarea>
                        </div>
                        <div class="field">
                            <label>{{ __('Credit limit (Rp)') }}</label>
                            <input type="number" step="0.01" min="0" class="input @error('creditLimit') invalid @enderror" wire:model="creditLimit">
                            @error('creditLimit') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Save changes') : __('Save customer') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
