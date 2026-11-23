<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Batches & Expiration Dates') }}</h1>
            <p>{{ __('Track batch numbers and remaining shelf life of agricultural chemicals.') }}</p>
        </div>
        <a href="{{ route('purchases.create') }}" wire:navigate class="btn btn-primary">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('New stock entry') }}
        </a>
    </div>

    @if ($flash)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $flash }}
        </div>
    @endif

    <div class="rail" style="flex-direction:row;flex-wrap:wrap;margin-bottom:16px;">
        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Batches tracked') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-packages style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value">{{ $this->summary['count'] }}</p>
                    <p class="stat-caption">{{ __(':count unit(s) in stock', ['count' => $this->summary['units']]) }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Stock value') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-cash style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value tabular">Rp {{ number_format($this->summary['value'], 0, ',', '.') }}</p>
                    <p class="stat-caption">{{ __('At purchase cost') }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Expiring soon') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-calendar style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value">{{ $this->summary['soon'] }}</p>
                    <p class="stat-caption">{{ __('Within :days days', ['days' => \App\Livewire\Batches\Index::SOON_WINDOW_DAYS]) }}</p>
                </div>
            </div>
        </div>

        <div class="card stat-card" style="flex:1 1 200px;">
            <div class="card-head" style="padding:0;">
                <p style="font-size:13.5px;font-weight:600;">{{ __('Expired') }}</p>
            </div>
            <div class="stat-body">
                <div class="stat-icon"><x-tabler-alert-triangle style="color:#1c1f1a" /></div>
                <div>
                    <p class="stat-value">{{ $this->summary['expired'] }}</p>
                    <p class="stat-caption">{{ __('Still holding stock') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding-bottom:0;">
            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" class="input" placeholder="{{ __('Search product or batch number…') }}" wire:model.live.debounce.300ms="search">
                    <select class="select" wire:model.live="categoryId">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select class="select" wire:model.live="shelfStatus">
                        <option value="">{{ __('All shelf life') }}</option>
                        <option value="fresh">{{ __('Fresh') }}</option>
                        <option value="soon">{{ __('Expiring soon') }}</option>
                        <option value="expired">{{ __('Expired') }}</option>
                        <option value="depleted">{{ __('Out of stock') }}</option>
                    </select>
                    <button type="button" class="btn btn-sm" wire:click="resetFilters">{{ __('Reset') }}</button>
                </div>
                <span class="cell-muted">{{ __(':count batch(es)', ['count' => $batches->total()]) }}</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Batch number') }}</th>
                        <th>{{ __('In stock') }}</th>
                        <th>{{ __('Manufactured') }}</th>
                        <th>{{ __('Expires') }}</th>
                        <th>{{ __('Shelf life') }}</th>
                        <th>{{ __('Stock value') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $days = $this->daysLeft($batch);
                            $depleted = (int) $batch->stock_quantity <= 0;
                        @endphp
                        <tr wire:key="batch-{{ $batch->id }}">
                            <td>
                                <p class="cell-strong">{{ $batch->product?->name ?? '—' }}</p>
                                <p class="cell-muted">
                                    {{ $batch->product?->category?->name ?? '—' }}
                                    @if ($batch->product?->active_ingredient) &middot; {{ $batch->product->active_ingredient }} @endif
                                </p>
                            </td>
                            <td class="cell-strong">{{ $batch->batch_number }}</td>
                            <td class="tabular">{{ $batch->stock_quantity }} {{ $batch->product?->unit }}</td>
                            <td class="cell-muted">
                                {{ $batch->manufactured_date?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="cell-muted">
                                {{ $batch->expiration_date?->locale(app()->getLocale())->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td>
                                @if ($depleted)
                                    <span class="badge badge-neutral">{{ __('Out of stock') }}</span>
                                @elseif ($days === null)
                                    <span class="badge badge-neutral">{{ __('No expiry date') }}</span>
                                @elseif ($days < 0)
                                    <span class="badge badge-bad">{{ __('Expired :days days ago', ['days' => abs($days)]) }}</span>
                                @elseif ($days <= \App\Livewire\Batches\Index::SOON_WINDOW_DAYS)
                                    <span class="badge badge-warn">{{ __(':days days left', ['days' => $days]) }}</span>
                                @else
                                    <span class="badge badge-good">{{ __(':days days left', ['days' => $days]) }}</span>
                                @endif
                            </td>
                            <td class="tabular">
                                Rp {{ number_format((int) $batch->stock_quantity * (float) ($batch->purchase_price ?? 0), 0, ',', '.') }}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="openEditModal({{ $batch->id }})" aria-label="{{ __('Edit') }}">
                                        <x-tabler-edit />
                                    </button>
                                    <button class="kebab" wire:click="askDelete({{ $batch->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><p class="table-empty">{{ __('No batches match your filters yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $batches->currentPage(), 'last' => $batches->lastPage()]) }}</span>
            {{ $batches->links() }}
        </div>
    </div>

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel">
                <div class="modal-head">
                    <h3>{{ __('Edit batch') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Batch number') }}</label>
                                <input type="text" class="input @error('batchNumber') invalid @enderror" wire:model="batchNumber">
                                @error('batchNumber') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Stock quantity') }}</label>
                                <input type="number" min="0" class="input @error('stockQuantity') invalid @enderror" wire:model="stockQuantity">
                                @error('stockQuantity') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Purchase price (Rp)') }}</label>
                                <input type="number" step="0.01" min="0" class="input @error('purchasePrice') invalid @enderror" wire:model="purchasePrice">
                                @error('purchasePrice') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Selling price (Rp)') }}</label>
                                <input type="number" step="0.01" min="0" class="input @error('sellingPrice') invalid @enderror" wire:model="sellingPrice">
                                @error('sellingPrice') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Manufactured') }} <span class="hint">({{ __('optional') }})</span></label>
                                <input type="date" class="input @error('manufacturedDate') invalid @enderror" wire:model="manufacturedDate">
                                @error('manufacturedDate') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Expires') }} <span class="hint">({{ __('optional') }})</span></label>
                                <input type="date" class="input @error('expirationDate') invalid @enderror" wire:model="expirationDate">
                                @error('expirationDate') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <p class="stat-caption">{{ __('Adjusting stock here overrides the quantity tracked by sales and purchases — use it for stock counts and corrections only.') }}</p>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
