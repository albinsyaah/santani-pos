@if ($deletingId)
    <div class="modal-backdrop" wire:click.self="cancelDelete">
        <div class="modal-panel" style="max-width:420px;">
            <div class="modal-head">
                <h3>{{ __('Delete :name?', ['name' => $deletingLabel]) }}</h3>
                <button class="kebab" wire:click="cancelDelete" aria-label="{{ __('Close') }}">
                    <x-tabler-x />
                </button>
            </div>
            <div class="modal-body">
                <p style="color:var(--ink-soft);font-size:13px;">{{ __('This action cannot be undone.') }}</p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn" wire:click="cancelDelete">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" wire:click="deleteConfirmed" wire:loading.attr="disabled" wire:target="deleteConfirmed">
                    <span wire:loading.remove wire:target="deleteConfirmed">{{ __('Yes, delete') }}</span>
                    <span wire:loading wire:target="deleteConfirmed">{{ __('Deleting…') }}</span>
                </button>
            </div>
        </div>
    </div>
@endif
