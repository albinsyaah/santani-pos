<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Pending Cart') }}</h1>
            <p>{{ __('Transactions held for later — resume any of them from where you left off.') }}</p>
        </div>
        <a href="{{ route('pos.terminal') }}" wire:navigate class="btn btn-primary">
            <x-tabler-plus stroke-width="2.5" />
            {{ __('New transaction') }}
        </a>
    </div>

    @if ($flash)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $flash }}
        </div>
    @endif

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Held at') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Note') }}</th>
                        <th>{{ __('Items') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingCarts as $pending)
                        <tr wire:key="pending-{{ $pending->id }}">
                            <td class="cell-muted">{{ $pending->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</td>
                            <td>{{ $pending->customer?->name ?? __('Walk-in customer') }}</td>
                            <td class="cell-muted">{{ $pending->note ?: '—' }}</td>
                            <td class="tabular">{{ $pending->item_count }}</td>
                            <td class="tabular">Rp {{ number_format($pending->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <a href="{{ route('pos.terminal', ['resume' => $pending->id]) }}" wire:navigate class="btn btn-sm">
                                        <x-tabler-player-play />
                                        {{ __('Resume') }}
                                    </a>
                                    <button class="kebab" wire:click="askDelete({{ $pending->id }})" aria-label="{{ __('Delete') }}">
                                        <x-tabler-trash />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><p class="table-empty">{{ __('No held carts right now.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>{{ __('Page :current of :last', ['current' => $pendingCarts->currentPage(), 'last' => $pendingCarts->lastPage()]) }}</span>
            {{ $pendingCarts->links() }}
        </div>
    </div>

    @include('partials.confirm-delete-modal')
</div>
