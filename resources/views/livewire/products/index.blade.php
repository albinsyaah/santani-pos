<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Product Data') }}</h1>
            <p>{{ __('Catalog of fertilizers, pesticides, seeds, and farming tools.') }}</p>
        </div>
        <button class="btn btn-primary" wire:click="openCreateModal">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('Add product') }}
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
            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" class="input" placeholder="{{ __('Search name, SKU, active ingredient…') }}" wire:model.live.debounce.300ms="search">
                    <select class="select" wire:model.live="categoryId">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <label class="checkbox-row">
                        <input type="checkbox" wire:model.live="lowStockOnly">
                        {{ __('Low stock only') }}
                    </label>
                </div>
                <span class="cell-muted">{{ __(':count product(s)', ['count' => $products->total()]) }}</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Selling price') }}</th>
                        <th>{{ __('Restricted') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td>
                                <p class="cell-strong">{{ $product->name }}</p>
                                <p class="cell-muted">{{ $product->sku }}@if($product->active_ingredient) &middot; {{ $product->active_ingredient }} @endif</p>
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->unit }}</td>
                            <td>
                                <span class="tabular">{{ $product->total_stock }}</span>
                                @if ($product->total_stock <= $product->min_stock_level)
                                    <span class="badge badge-bad">{{ __('Low') }}</span>
                                @endif
                            </td>
                            <td class="tabular">Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</td>
                            <td>
                                @if ($product->is_restricted)
                                    <span class="badge badge-warn">{{ __('Restricted') }}</span>
                                @else
                                    <span class="badge badge-neutral">{{ __('Standard') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="kebab" wire:click="openEditModal({{ $product->id }})" aria-label="{{ __('Edit') }}">
                                        <x-tabler-edit />
                                    </button>
                                    <button class="kebab" wire:click="askDelete({{ $product->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <p class="table-empty">{{ __('No products match your filters yet.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $products->currentPage(), 'last' => $products->lastPage()]) }}</span>
            {{ $products->links() }}
        </div>
    </div>

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel">
                <div class="modal-head">
                    <h3>{{ $editingId ? __('Edit product') : __('Add product') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Product name') }}</label>
                            <input type="text" class="input @error('name') invalid @enderror" wire:model="name" placeholder="{{ __('e.g. NPK 16-16-16 Fertilizer') }}">
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('SKU') }} <span class="hint">({{ __('auto-filled') }})</span></label>
                                <input type="text" class="input @error('sku') invalid @enderror" wire:model="sku" placeholder="{{ __('e.g. FRT-NPK-25KG') }}">
                                @error('sku') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Unit') }}</label>
                                <select class="select" wire:model="unit">
                                    <option value="kg">kg</option>
                                    <option value="liter">liter</option>
                                    <option value="sack">sack</option>
                                    <option value="bottle">bottle</option>
                                    <option value="pcs">pcs</option>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Category') }}</label>
                            <select class="select @error('formCategoryId') invalid @enderror" wire:model="formCategoryId">
                                <option value="">{{ __('Select a category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('formCategoryId') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label>{{ __('Active ingredient') }} <span class="hint">({{ __('optional') }})</span></label>
                            <input type="text" class="input" wire:model="activeIngredient" placeholder="{{ __('e.g. Glyphosate 480 SL') }}">
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Purchase price (Rp)') }}</label>
                                <input type="number" step="0.01" class="input @error('purchasePrice') invalid @enderror" wire:model="purchasePrice">
                                @error('purchasePrice') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Selling price (Rp)') }}</label>
                                <input type="number" step="0.01" class="input @error('sellingPrice') invalid @enderror" wire:model="sellingPrice">
                                @error('sellingPrice') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Minimum stock level') }}</label>
                            <input type="number" class="input @error('minStockLevel') invalid @enderror" wire:model="minStockLevel">
                            @error('minStockLevel') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <label class="checkbox-row">
                            <input type="checkbox" wire:model="isRestricted">
                            {{ __('Restricted item (requires ID / license to sell)') }}
                        </label>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Save changes') : __('Save product') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
