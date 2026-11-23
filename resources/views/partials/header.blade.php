<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="icon-btn square burger" aria-label="{{ __('Menu') }}"
                :aria-expanded="sidebarOpen.toString()" aria-controls="app-sidebar"
                @click="sidebarOpen = !sidebarOpen">
            <x-tabler-menu-2 />
        </button>

        <a href="{{ route('dashboard') }}" wire:navigate class="brand">
            <span class="brand-mark">
                <x-tabler-leaf stroke-width="2.5" style="color:#c6f135" />
            </span>
            Santani
        </a>
    </div>

    <div class="topbar-tools">
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
        <button class="icon-btn topbar-date">
            <x-tabler-calendar />
            {{ now()->locale(app()->getLocale())->translatedFormat('d F Y') }}
        </button>
        <button class="icon-btn">
            <x-tabler-user-circle />
            <span class="owner-label">{{ $currentUser?->role?->name ?? __('Guest') }}</span>
        </button>
    </div>
</header>
