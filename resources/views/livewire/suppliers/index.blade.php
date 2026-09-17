<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Suppliers') }}</h1>
            <p>{{ __('Vendors you buy fertilizers and chemicals from.') }}</p>
        </div>
        <button class="btn btn-primary" wire:click="openCreateModal">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('Add supplier') }}
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
            <input type="text" class="input" style="max-width:320px;" placeholder="{{ __('Search supplier name…') }}" wire:model.live.debounce.300ms="search">
        </div>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Contact person') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Address') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr wire:key="supplier-{{ $supplier->id }}">
                            <td class="cell-strong">{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?? '—' }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td class="cell-muted">{{ $supplier->address ?? '—' }}</td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="openEditModal({{ $supplier->id }})" aria-label="{{ __('Edit') }}">
                                        <x-tabler-edit />
                                    </button>
                                    <button class="kebab" wire:click="askDelete({{ $supplier->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><p class="table-empty">{{ __('No suppliers yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $suppliers->currentPage(), 'last' => $suppliers->lastPage()]) }}</span>
            {{ $suppliers->links() }}
        </div>
    </div>

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel">
                <div class="modal-head">
                    <h3>{{ $editingId ? __('Edit supplier') : __('Add supplier') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Supplier name') }}</label>
                            <input type="text" class="input @error('name') invalid @enderror" wire:model="name">
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Contact person') }}</label>
                                <input type="text" class="input" wire:model="contactPerson">
                            </div>
                            <div class="field">
                                <label>{{ __('Phone') }}</label>
                                <input type="text" class="input" wire:model="phone">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Address') }}</label>
                            <textarea class="textarea" wire:model="address"></textarea>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Save changes') : __('Save supplier') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
