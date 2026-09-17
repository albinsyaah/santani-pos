<footer class="app-footer">
    <span>&copy; {{ now()->year }} {{ config('app.name') }} &middot; {{ __('Fertilizer & agrochemical point of sale') }}</span>
    <span>{{ __('Built with Laravel :version', ['version' => app()->version()]) }} &amp; <a href="https://livewire.laravel.com" target="_blank" rel="noopener">Livewire</a></span>
</footer>
