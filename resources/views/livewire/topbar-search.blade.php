<div class="search-wrap">
    <input
        type="search"
        class="search-input"
        placeholder="{{ __('Search products, farmers…') }}"
        wire:model.live.debounce.300ms="query"
        autocomplete="off"
    >

    @if (mb_strlen($query) >= 2)
        <div class="search-results">
            <div class="search-results-group">{{ __('Products') }}</div>
            @forelse ($this->products as $product)
                <a href="{{ route('products.index', ['search' => $product->name]) }}" wire:navigate class="search-result-row">
                    <span>{{ $product->name }}</span>
                    <span class="cell-muted">{{ $product->sku }}</span>
                </a>
            @empty
                <div class="search-result-empty">{{ __('No matching products.') }}</div>
            @endforelse

            <div class="search-results-group">{{ __('Farmers') }}</div>
            @forelse ($this->customers as $customer)
                <a href="{{ route('customers.index', ['search' => $customer->name]) }}" wire:navigate class="search-result-row">
                    <span>{{ $customer->name }}</span>
                    <span class="cell-muted">{{ $customer->phone ?? '—' }}</span>
                </a>
            @empty
                <div class="search-result-empty">{{ __('No matching farmers.') }}</div>
            @endforelse
        </div>
    @endif
</div>
