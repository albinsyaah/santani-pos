<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Incoming Stock Entry') }}</h1>
            <p>{{ __('Receive stock from a supplier: pick the products, quantities and prices.') }}</p>
        </div>
        <a href="{{ route('purchases.index') }}" wire:navigate class="btn">
            <x-tabler-history />
            {{ __('Purchase History') }}
        </a>
    </div>

    @if ($error)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;background:var(--bad);">
            <x-tabler-alert-circle />
            {{ $error }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="content-grid">
            <div style="display:flex;flex-direction:column;gap:16px;min-width:0;">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <p style="font-size:13.5px;font-weight:600;">{{ __('Delivery details') }}</p>
                            <p style="font-size:11px;color:var(--ink-soft);margin-top:2px;">{{ __('Who delivered this stock and under which reference.') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="field">
                                <label>{{ __('Supplier') }}</label>
                                <select class="select @error('supplierId') invalid @enderror" wire:model="supplierId">
                                    <option value="">{{ __('Select a supplier') }}</option>
                                    @foreach ($this->suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplierId') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>{{ __('Purchase #') }}</label>
                                <input type="text" class="input @error('purchaseNumber') invalid @enderror" wire:model="purchaseNumber">
                                @error('purchaseNumber') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Note') }} <span class="hint">({{ __('optional') }})</span></label>
                            <input type="text" class="input" wire:model="note" placeholder="{{ __('e.g. Delivery note DN-4471, driver Pak Slamet') }}">
                            @error('note') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <div>
                            <p style="font-size:13.5px;font-weight:600;">{{ __('Stock received') }}</p>
                            <p style="font-size:11px;color:var(--ink-soft);margin-top:2px;">{{ __('Pick the product, quantity and prices. Batch numbers are filled in automatically; a changed price also updates the product.') }}</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="tally-head">
                            <span>{{ __('Product') }}</span>
                            <span>{{ __('Quantity') }}</span>
                        </div>

                        @foreach ($lines as $index => $line)
                            <div class="tally-line" wire:key="line-{{ $index }}">
                            <div class="tally-row">
                                <div class="tally-product">
                                    <label class="tally-label">{{ __('Product') }}</label>
                                    @if ($line['product_id'] && ($selected = $this->selectedProducts->get($line['product_id'])))
                                        <div class="product-picked">
                                            <span>
                                                <strong>{{ $selected->name }}</strong>
                                                <span class="hint hide-mobile">{{ $selected->sku }} &middot; {{ $selected->unit }}</span>
                                            </span>
                                            <button type="button" class="kebab" wire:click="clearProduct({{ $index }})" aria-label="{{ __('Change product') }}">
                                                <x-tabler-x />
                                            </button>
                                        </div>
                                    @else
                                        <input type="search" class="input @error('lines.'.$index.'.product_id') invalid @enderror"
                                               wire:model.live.debounce.250ms="lines.{{ $index }}.search"
                                               placeholder="{{ __('Search product by name, SKU or barcode…') }}" autocomplete="off">
                                        @if (trim($line['search']) !== '')
                                            @php $matches = $this->searchProducts($line['search']); @endphp
                                            <div class="product-results">
                                                @forelse ($matches as $match)
                                                    <button type="button" class="product-result" wire:key="match-{{ $index }}-{{ $match->id }}"
                                                            wire:click="selectProduct({{ $index }}, {{ $match->id }})">
                                                        <span>{{ $match->name }}</span>
                                                        <span class="hint hide-mobile">{{ $match->sku }} &middot; {{ $match->unit }}</span>
                                                    </button>
                                                @empty
                                                    <p class="product-result-empty">{{ __('No products found.') }}</p>
                                                @endforelse
                                            </div>
                                        @endif
                                    @endif
                                    @error('lines.'.$index.'.product_id') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="tally-qty">
                                    <label class="tally-label">{{ __('Quantity') }}</label>
                                    <input type="number" min="1" class="input @error('lines.'.$index.'.quantity') invalid @enderror"
                                           wire:model.live.debounce.400ms="lines.{{ $index }}.quantity" placeholder="0" aria-label="{{ __('Quantity') }}">
                                    @error('lines.'.$index.'.quantity') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                @if (count($lines) > 1)
                                    <button type="button" class="kebab" wire:click="removeLine({{ $index }})" aria-label="{{ __('Remove row') }}">
                                        <x-tabler-trash />
                                    </button>
                                @else
                                    <span class="kebab-spacer"></span>
                                @endif
                            </div>

                            <div class="tally-prices">
                                <div class="field">
                                    <label>{{ __('Purchase price (Rp)') }}</label>
                                    <input type="number" step="0.01" min="0" class="input @error('lines.'.$index.'.unit_cost') invalid @enderror" wire:model.live.debounce.400ms="lines.{{ $index }}.unit_cost" placeholder="0">
                                    @error('lines.'.$index.'.unit_cost') <span class="error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label>{{ __('Selling price (Rp)') }}</label>
                                    <input type="number" step="0.01" min="0" class="input @error('lines.'.$index.'.selling_price') invalid @enderror" wire:model="lines.{{ $index }}.selling_price" placeholder="0">
                                    @error('lines.'.$index.'.selling_price') <span class="error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            </div>
                        @endforeach

                        <button type="button" class="btn btn-sm tally-add" wire:click="addLine">
                            <x-tabler-plus stroke-width="2.5" />
                            {{ __('Add row') }}
                        </button>
                    </div>

                    @error('lines')
                        <div class="card-body" style="padding-top:0;"><span class="error">{{ $message }}</span></div>
                    @enderror
                </div>
            </div>

            <div class="rail">
                <div class="card stat-card">
                    <div class="card-head" style="padding:0;">
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Entry summary') }}</p>
                    </div>
                    <div class="stat-body">
                        <div class="stat-icon">
                            <x-tabler-packages style="color:#1c1f1a" />
                        </div>
                        <div>
                            <p class="stat-value">{{ __(':count unit(s)', ['count' => $this->totalUnits]) }}</p>
                            <p class="stat-caption">{{ __(':count batch row(s)', ['count' => count($lines)]) }}</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Payment') }}</p>
                    </div>
                    <div class="card-body">
                        <div class="cart-summary-row">
                            <span>{{ __('Total cost') }}</span>
                            <span class="tabular">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                        </div>

                        <div class="field" style="margin-top:12px;">
                            <label>{{ __('Paid to supplier (Rp)') }}</label>
                            <input type="number" step="0.01" min="0" class="input @error('paidAmount') invalid @enderror" wire:model.live.debounce.400ms="paidAmount" placeholder="0">
                            @error('paidAmount') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="cart-summary-row total">
                            <span>{{ __('Balance due') }}</span>
                            <span class="tabular">Rp {{ number_format($this->balanceDue, 0, ',', '.') }}</span>
                        </div>

                        <p class="stat-caption" style="margin-top:10px;">
                            {{ __('Paid in full by default. Lower or clear the amount to record the rest as supplier credit.') }}
                        </p>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top:14px;" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Save stock entry') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
