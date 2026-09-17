<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Point of sale') }}</h1>
            <p>{{ __('Sell fertilizers and agrochemicals — stock is drawn oldest-batch-first automatically.') }}</p>
        </div>
    </div>

    @if ($success)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $success }}
        </div>
    @endif
    @if ($error)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;background:var(--bad);">
            <x-tabler-alert-circle />
            {{ $error }}
        </div>
    @endif

    <div class="pos-grid">

        <!-- PRODUCT PICKER -->
        <div class="card">
            <div class="card-body" style="padding-bottom:0;">
                <input type="text" class="input" placeholder="{{ __('Search products by name or SKU…') }}" wire:model.live.debounce.300ms="search">
            </div>
            <div class="card-body">
                <div class="pos-product-grid">
                    @forelse ($this->products as $product)
                        @php $inCart = $cart[$product->id]['quantity'] ?? 0; @endphp
                        <button
                            type="button"
                            class="pos-product"
                            wire:click="addToCart({{ $product->id }})"
                            @if ($product->total_stock <= 0) disabled @endif
                        >
                            <p class="name">{{ $product->name }}</p>
                            <p class="meta">
                                {{ $product->category?->name ?? __('Uncategorized') }} &middot;
                                {{ __(':qty :unit left', ['qty' => $product->total_stock, 'unit' => $product->unit]) }}
                                @if ($inCart) &middot; {{ __(':count in cart', ['count' => $inCart]) }} @endif
                            </p>
                            <p class="price tabular">Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</p>
                        </button>
                    @empty
                        <p class="table-empty">{{ __('No products found.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- CART -->
        <aside class="rail">
            <div class="card">
                <div class="card-head">
                    <h2>{{ __('Current cart') }}</h2>
                    <span class="cell-muted">{{ __(':count item(s)', ['count' => count($cart)]) }}</span>
                </div>
                <div class="card-body">
                    @forelse ($cart as $productId => $line)
                        <div class="cart-row" wire:key="cart-{{ $productId }}">
                            <div style="flex:1;min-width:0;">
                                <p class="name">{{ $line['name'] }}</p>
                                <p class="price tabular">Rp {{ number_format($line['unit_price'], 0, ',', '.') }} / {{ $line['unit'] }}</p>
                            </div>
                            <div class="qty-stepper">
                                <button type="button" wire:click="decrementQty({{ $productId }})" aria-label="{{ __('Decrease') }}">−</button>
                                <span class="tabular">{{ $line['quantity'] }}</span>
                                <button type="button" wire:click="incrementQty({{ $productId }})" aria-label="{{ __('Increase') }}">+</button>
                            </div>
                        </div>
                    @empty
                        <p class="table-empty">{{ __('Cart is empty — tap a product to add it.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="card card-body">
                <div class="field">
                    <label>{{ __('Farmer / customer') }} <span class="hint">({{ __('required for credit sales') }})</span></label>
                    <select class="select" wire:model="customerId">
                        <option value="">{{ __('Walk-in customer') }}</option>
                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}@if($customer->farmer_group) ({{ $customer->farmer_group }}) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>{{ __('Payment method') }}</label>
                    <select class="select" wire:model="paymentMethod">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="qris">{{ __('QRIS') }}</option>
                        <option value="transfer">{{ __('Bank transfer') }}</option>
                        <option value="credit">{{ __('Credit (harvest-time payment)') }}</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label>{{ __('Discount (Rp)') }}</label>
                        <input type="number" step="0.01" min="0" class="input" wire:model.live="discount">
                    </div>
                    <div class="field">
                        <label>{{ __('Tax (Rp)') }}</label>
                        <input type="number" step="0.01" min="0" class="input" wire:model.live="tax">
                    </div>
                </div>

                <div class="cart-summary-row"><span>{{ __('Subtotal') }}</span><span class="tabular">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span></div>
                <div class="cart-summary-row total"><span>{{ __('Total due') }}</span><span class="tabular">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span></div>

                <div class="field" style="margin-top:14px;">
                    <label>{{ __('Amount paid (Rp)') }}</label>
                    <input type="number" step="0.01" min="0" class="input" wire:model.live="paidAmount" placeholder="0">
                </div>

                @if ($this->changeAmount > 0)
                    <div class="cart-summary-row"><span>{{ __('Change') }}</span><span class="tabular">Rp {{ number_format($this->changeAmount, 0, ',', '.') }}</span></div>
                @endif

                <div style="display:flex;gap:8px;margin-top:10px;">
                    <button type="button" class="btn" style="flex:1;" wire:click="holdCart" wire:loading.attr="disabled" wire:target="holdCart" @if(empty($cart)) disabled @endif>
                        <x-tabler-player-pause />
                        {{ __('Hold cart') }}
                    </button>
                    <button type="button" class="btn btn-primary" style="flex:1;" wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout">
                        <span wire:loading.remove wire:target="checkout">{{ __('Complete sale') }}</span>
                        <span wire:loading wire:target="checkout">{{ __('Processing…') }}</span>
                    </button>
                </div>
            </div>
        </aside>
    </div>
</div>
