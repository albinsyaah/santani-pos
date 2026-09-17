@php
    $initials = $currentUser
        ? collect(explode(' ', $currentUser->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('')
        : 'AP';
    $posGroupActive = request()->routeIs('pos.*') || request()->routeIs('sales.*');
    $productsGroupActive = request()->routeIs('products.*') || request()->routeIs('batches.*') || request()->routeIs('categories.*');
    $purchasesGroupActive = request()->routeIs('purchases.*');
    $farmersGroupActive = request()->routeIs('customers.*') || request()->routeIs('customer-payments.*');
    $reportsGroupActive = request()->routeIs('reports.*');
@endphp
<aside class="sidebar">
    <p class="side-label">{{ __('Main menu') }}</p>
    <nav class="side-nav" aria-label="Main">
        <a href="{{ route('dashboard') }}" wire:navigate class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <x-tabler-layout-dashboard />
            <span>{{ __('Dashboard') }}</span>
        </a>

        <details class="side-group" @if($posGroupActive) open @endif>
            <summary class="side-link {{ $posGroupActive ? 'active' : '' }}">
                <span class="side-group-label">
                    <x-tabler-shopping-cart />
                    <span>{{ __('Point of sale') }}</span>
                </span>
                <x-tabler-chevron-right class="chevron" />
            </summary>
            <div class="side-submenu">
                <a href="{{ route('pos.terminal') }}" wire:navigate class="side-sublink {{ request()->routeIs('pos.terminal') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('New Sale') }}</span>
                </a>
                <a href="{{ route('pos.pending') }}" wire:navigate class="side-sublink {{ request()->routeIs('pos.pending') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Pending Cart') }}</span>
                </a>
                <a href="{{ route('sales.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Sales History') }}</span>
                </a>
            </div>
        </details>

        <details class="side-group" @if($productsGroupActive) open @endif>
            <summary class="side-link {{ $productsGroupActive ? 'active' : '' }}">
                <span class="side-group-label">
                    <x-tabler-box />
                    <span>{{ __('Products') }}</span>
                </span>
                <x-tabler-chevron-right class="chevron" />
            </summary>
            <div class="side-submenu">
                <a href="{{ route('products.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Product Data') }}</span>
                </a>
                <a href="{{ route('batches.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Batches & Expiration Dates') }}</span>
                </a>
                <a href="{{ route('categories.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Categories & Active Ingredients') }}</span>
                </a>
            </div>
        </details>
        <details class="side-group" @if($purchasesGroupActive) open @endif>
            <summary class="side-link {{ $purchasesGroupActive ? 'active' : '' }}">
                <span class="side-group-label">
                    <x-tabler-truck-delivery />
                    <span>{{ __('Purchases') }}</span>
                </span>
                <x-tabler-chevron-right class="chevron" />
            </summary>
            <div class="side-submenu">
                <a href="{{ route('purchases.create') }}" wire:navigate class="side-sublink {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Incoming Stock Entry') }}</span>
                </a>
                <a href="{{ route('purchases.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Purchase History') }}</span>
                </a>
            </div>
        </details>
        <details class="side-group" @if($farmersGroupActive) open @endif>
            <summary class="side-link {{ $farmersGroupActive ? 'active' : '' }}">
                <span class="side-group-label">
                    <x-tabler-users />
                    <span>{{ __('Farmers') }}</span>
                </span>
                <x-tabler-chevron-right class="chevron" />
            </summary>
            <div class="side-submenu">
                <a href="{{ route('customers.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Farmers & Customers') }}</span>
                </a>
                <a href="{{ route('customer-payments.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('customer-payments.*') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Receivables Payments') }}</span>
                </a>
            </div>
        </details>
        <a href="{{ route('suppliers.index') }}" wire:navigate class="side-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <x-tabler-building-warehouse />
            <span>{{ __('Suppliers') }}</span>
        </a>
        <details class="side-group" @if($reportsGroupActive) open @endif>
            <summary class="side-link {{ $reportsGroupActive ? 'active' : '' }}">
                <span class="side-group-label">
                    <x-tabler-chart-bar />
                    <span>{{ __('Reports') }}</span>
                </span>
                <x-tabler-chevron-right class="chevron" />
            </summary>
            <div class="side-submenu">
                <a href="{{ route('reports.index') }}" wire:navigate class="side-sublink {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Overview') }}</span>
                </a>
                <a href="{{ route('reports.sales') }}" wire:navigate class="side-sublink {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Sales Report') }}</span>
                </a>
                <a href="{{ route('reports.receivables') }}" wire:navigate class="side-sublink {{ request()->routeIs('reports.receivables') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Farmer Receivables Report') }}</span>
                </a>
                <a href="{{ route('reports.expirations') }}" wire:navigate class="side-sublink {{ request()->routeIs('reports.expirations') ? 'active' : '' }}">
                    <x-tabler-point />
                    <span>{{ __('Expired Pesticides Report') }}</span>
                </a>
            </div>
        </details>
    </nav>

    <hr class="side-divider">

    <p class="side-label">{{ __('Support') }}</p>
    <nav class="side-nav" aria-label="Support">
        <a href="{{ route('settings.index') }}" wire:navigate class="side-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <x-tabler-settings />
            <span>{{ __('Settings') }}</span>
        </a>
        <a href="#" class="side-link">
            <x-tabler-alert-triangle />
            <span>{{ __('Restricted items policy') }}</span>
        </a>
        <a href="#" class="side-link">
            <x-tabler-help-circle />
            <span>{{ __('Help centre') }}</span>
        </a>
    </nav>

    <div class="side-account">
        <div class="avatar">{{ $initials }}</div>
        <div>
            <p class="side-account-name">{{ $currentUser->name ?? __('Guest user') }}</p>
            <p class="side-account-email">{{ $currentUser->email ?? '—' }}</p>
        </div>
    </div>
</aside>
