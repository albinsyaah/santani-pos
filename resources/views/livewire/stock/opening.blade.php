<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Add Current Stock') }}</h1>
            <p>{{ __('Record the inventory you already have on the shelf, with batch numbers and expiration dates.') }}</p>
        </div>
        <a href="{{ route('batches.index') }}" wire:navigate class="btn">
            <x-tabler-packages />
            {{ __('Batches & Expiration Dates') }}
        </a>
    </div>

    @if ($flash)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $flash }}
        </div>
    @endif

    @if ($error)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;background:var(--bad);">
            <x-tabler-alert-circle />
            {{ $error }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="content-grid">
            <div class="card" style="min-width:0;">
                <div class="card-head">
                    <div>
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Stock on hand') }}</p>
                        <p style="font-size:11px;color:var(--ink-soft);margin-top:2px;">{{ __('Each row creates a batch, or tops up the batch if the batch number already exists for that product.') }}</p>
                    </div>
                    <button type="button" class="btn btn-sm" wire:click="addLine">
                        <x-tabler-plus stroke-width="2.5" />
                        {{ __('Add row') }}
                    </button>
                </div>

                <div class="entry-lines">
                    @foreach ($lines as $index => $line)
                        <div class="entry-line" wire:key="line-{{ $index }}">
                            <div class="entry-line-head">
                                <span class="entry-line-title">{{ __('Item :number', ['number' => $index + 1]) }}</span>
                                @if (count($lines) > 1)
                                    <button type="button" class="kebab" wire:click="removeLine({{ $index }})" aria-label="{{ __('Remove row') }}">
                                        <x-tabler-trash />
                                    </button>
                                @endif
                            </div>

                            <div class="entry-grid">
                                <div class="field span-2">
                                    <label>{{ __('Product') }}</label>
                                    <select class="select @error('lines.'.$index.'.product_id') invalid @enderror" wire:model.live="lines.{{ $index }}.product_id">
                                        <option value="">{{ __('Select a product') }}</option>
                                        @foreach ($this->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->unit }})</option>
                                        @endforeach
                                    </select>
                                    @error('lines.'.$index.'.product_id') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Batch number') }}</label>
                                    <input type="text" class="input @error('lines.'.$index.'.batch_number') invalid @enderror" wire:model="lines.{{ $index }}.batch_number" placeholder="{{ __('e.g. B-2026-08') }}">
                                    @error('lines.'.$index.'.batch_number') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Quantity') }}</label>
                                    <input type="number" min="1" class="input @error('lines.'.$index.'.quantity') invalid @enderror" wire:model.live.debounce.400ms="lines.{{ $index }}.quantity" placeholder="0">
                                    @error('lines.'.$index.'.quantity') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Purchase price (Rp)') }}</label>
                                    <input type="number" step="0.01" min="0" class="input @error('lines.'.$index.'.purchase_price') invalid @enderror" wire:model.live.debounce.400ms="lines.{{ $index }}.purchase_price" placeholder="0">
                                    @error('lines.'.$index.'.purchase_price') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Selling price (Rp)') }}</label>
                                    <input type="number" step="0.01" min="0" class="input @error('lines.'.$index.'.selling_price') invalid @enderror" wire:model="lines.{{ $index }}.selling_price" placeholder="0">
                                    @error('lines.'.$index.'.selling_price') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Manufactured') }} <span class="hint">({{ __('optional') }})</span></label>
                                    <input type="date" class="input @error('lines.'.$index.'.manufactured_date') invalid @enderror" wire:model="lines.{{ $index }}.manufactured_date">
                                    @error('lines.'.$index.'.manufactured_date') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label>{{ __('Expires') }} <span class="hint">({{ __('optional') }})</span></label>
                                    <input type="date" class="input @error('lines.'.$index.'.expiration_date') invalid @enderror" wire:model="lines.{{ $index }}.expiration_date">
                                    @error('lines.'.$index.'.expiration_date') <span class="error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('lines')
                    <div class="card-body" style="padding-top:0;"><span class="error">{{ $message }}</span></div>
                @enderror
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
                    <div class="card-body">
                        <div class="cart-summary-row total">
                            <span>{{ __('Stock value (at purchase price)') }}</span>
                            <span class="tabular">Rp {{ number_format($this->totalValue, 0, ',', '.') }}</span>
                        </div>

                        <p class="stat-caption" style="margin-top:10px;">
                            {{ __('No supplier or payment is recorded. Use Incoming Stock Entry for new deliveries.') }}
                        </p>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top:14px;" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Save stock') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
