<header class="topbar">
    <a href="{{ route('dashboard') }}" wire:navigate class="brand">
        <span class="brand-mark">
            <x-tabler-leaf stroke-width="2.5" style="color:#c6f135" />
        </span>
        Agro<b>POS</b>
    </a>

    <div class="topbar-tools">
        <livewire:topbar-search />

        <div class="lang-switch" role="group" aria-label="{{ __('Language') }}">
            <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            <a href="{{ route('locale.switch', 'id') }}" class="{{ app()->getLocale() === 'id' ? 'active' : '' }}">ID</a>
        </div>

        <button class="icon-btn notif square" aria-label="{{ __('Notifications') }}">
            <x-tabler-bell />
            <span class="notif-dot"></span>
        </button>
    </div>

    <div class="topbar-right">
        <button class="icon-btn">
            <x-tabler-calendar />
            {{ now()->locale(app()->getLocale())->translatedFormat('d F Y') }}
        </button>
        <button class="icon-btn">
            <x-tabler-user-circle />
            {{ $currentUser?->role?->name ?? __('Guest') }}
        </button>
    </div>
</header>
