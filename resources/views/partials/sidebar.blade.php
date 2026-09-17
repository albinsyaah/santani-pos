@php
    $initials = $currentUser
        ? collect(explode(' ', $currentUser->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('')
        : 'AP';
    $posGroupActive = request()->routeIs('pos.*') || request()->routeIs('sales.*');
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

        <a href="{{ route('products.index') }}" wire:navigate class="side-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <x-tabler-box />
            <span>{{ __('Products') }}</span>
        </a>
        <a href="{{ route('purchases.index') }}" wire:navigate class="side-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
            <x-tabler-truck-delivery />
            <span>{{ __('Purchases') }}</span>
        </a>
        <a href="{{ route('customers.index') }}" wire:navigate class="side-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <x-tabler-users />
            <span>{{ __('Farmers') }}</span>
        </a>
        <a href="{{ route('suppliers.index') }}" wire:navigate class="side-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <x-tabler-building-warehouse />
            <span>{{ __('Suppliers') }}</span>
        </a>
        <a href="{{ route('reports.index') }}" wire:navigate class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <x-tabler-chart-bar />
            <span>{{ __('Reports') }}</span>
        </a>
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
