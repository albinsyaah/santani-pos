<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Sales Tally') }}</h1>
            <p>{{ __('Key in what was sold when the cashier was not used: product, quantity and date.') }}</p>
        </div>
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
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Products sold') }}</p>
                        <p style="font-size:11px;color:var(--ink-soft);margin-top:2px;">{{ __('Saved as a cash sale at the current product price. Stock is deducted, earliest expiry first.') }}</p>
                    </div>
                </div>

                <div class="entry-lines">
                    <div class="entry-line">
                        <div class="entry-grid">
                            <div class="field">
                                <label>{{ __('Date') }}</label>
                                <input type="date" class="input @error('date') invalid @enderror" wire:model.live="date" max="{{ now()->format('Y-m-d') }}">
                                @error('date') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

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
                                    @if ($line['product_id'] && ($selected = $this->selectedProducts->get($line['product_id'])))
                                        <div class="product-picked">
                                            <span>
                                                <strong>{{ $selected->name }}</strong>
                                                <span class="hint">Rp {{ number_format((float) $selected->selling_price, 0, ',', '.') }}<span class="hide-mobile">/{{ $selected->unit }}</span> &middot; {{ __('Stock') }}: {{ $selected->total_stock }}</span>
                                            </span>
                                            <button type="button" class="btn btn-sm" wire:click="clearProduct({{ $index }})">
                                                {{ __('Change product') }}
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

                                <div class="field">
                                    <label>{{ __('Quantity sold') }}</label>
                                    <input type="number" min="1" class="input @error('lines.'.$index.'.quantity') invalid @enderror" wire:model.live.debounce.400ms="lines.{{ $index }}.quantity" placeholder="0">
                                    @error('lines.'.$index.'.quantity') <span class="error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <button type="button" class="btn btn-sm btn-block" wire:click="addLine">
                        <x-tabler-plus stroke-width="2.5" />
                        {{ __('Add row') }}
                    </button>
                </div>

                @error('lines')
                    <div class="card-body" style="padding-top:0;"><span class="error">{{ $message }}</span></div>
                @enderror
            </div>

            <div class="rail">
                <div class="card stat-card" style="flex-basis:auto;">
                    <div class="card-head" style="padding:0;">
                        <p style="font-size:13.5px;font-weight:600;">{{ __('Tally summary') }}</p>
                    </div>
                    <div class="stat-body">
                        <div class="stat-icon">
                            <x-tabler-shopping-cart style="color:#1c1f1a" />
                        </div>
                        <div>
                            <p class="stat-value">{{ __(':count unit(s)', ['count' => $this->totalUnits]) }}</p>
                            <p class="stat-caption">{{ __(':count item row(s)', ['count' => count($lines)]) }}</p>
                        </div>
                    </div>
                </div>

                <div class="card" style="flex-basis:auto;">
                    <div class="card-body">
                        <div class="cart-summary-row total">
                            <span>{{ __('Total sales') }}</span>
                            <span class="tabular">Rp {{ number_format($this->totalValue, 0, ',', '.') }}</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top:14px;" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Save tally') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card" style="margin-top:20px;">
        <div class="card-head">
            <p style="font-size:13.5px;font-weight:600;">{{ __('Already tallied on this date') }}</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Quantity sold') }}</th>
                        <th>{{ __('Total sales') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recap as $row)
                        <tr>
                            <td class="cell-strong">{{ $row['name'] }}</td>
                            <td class="tabular">{{ $row['quantity'] }} {{ $row['unit'] }}</td>
                            <td class="tabular">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><p class="table-empty">{{ __('Nothing tallied for this date yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
