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
        <div class="card" style="max-width:760px;">
            <div class="card-body">
                <div class="tally-date field">
                    <label>{{ __('Date') }}</label>
                    <input type="date" class="input @error('date') invalid @enderror" wire:model.live="date" max="{{ now()->format('Y-m-d') }}">
                    @error('date') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="tally-head">
                    <span>{{ __('Product') }}</span>
                    <span>{{ __('Quantity sold') }}</span>
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
                                        <span class="hint">Rp {{ number_format((float) $selected->selling_price, 0, ',', '.') }}<span class="hide-mobile">/{{ $selected->unit }}</span> &middot; {{ __('Stock') }}: {{ $selected->total_stock }}</span>
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
                            <label class="tally-label">{{ __('Quantity sold') }}</label>
                            <input type="number" min="1" class="input @error('lines.'.$index.'.quantity') invalid @enderror"
                                   wire:model.live.debounce.400ms="lines.{{ $index }}.quantity" placeholder="0" aria-label="{{ __('Quantity sold') }}">
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
                    </div>
                @endforeach

                @error('lines') <span class="error">{{ $message }}</span> @enderror

                <button type="button" class="btn btn-sm tally-add" wire:click="addLine">
                    <x-tabler-plus stroke-width="2.5" />
                    {{ __('Add row') }}
                </button>
            </div>

            <div class="tally-foot">
                <span>
                    {{ __('Total sales') }}:
                    <strong class="tabular">Rp {{ number_format($this->totalValue, 0, ',', '.') }}</strong>
                </span>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('Save tally') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                </button>
            </div>
        </div>
    </form>

    <div class="card" style="margin-top:20px;max-width:760px;">
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
