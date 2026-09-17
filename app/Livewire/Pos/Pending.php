<?php

namespace App\Livewire\Pos;

use App\Models\PendingCart;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pending Cart')]
class Pending extends Component
{
    use WithPagination;

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public string $flash = '';

    public function askDelete(int $pendingId): void
    {
        $pending = PendingCart::findOrFail($pendingId);
        $this->deletingId = $pending->id;
        $this->deletingLabel = $pending->note ?: __('this held cart');
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingLabel = '';
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deletingId) {
            return;
        }

        PendingCart::find($this->deletingId)?->delete();
        $this->flash = __('The held cart was discarded.');
        $this->cancelDelete();
    }

    public function render()
    {
        $pendingCarts = PendingCart::query()
            ->with('customer')
            ->latest()
            ->paginate(10);

        return view('livewire.pos.pending', ['pendingCarts' => $pendingCarts]);
    }
}
