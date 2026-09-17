<?php

namespace App\Livewire\Reports;

use App\Models\ProductBatch;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Expired pesticides report')]
class Expirations extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'window', history: true)]
    public string $windowDays = '30';

    #[Url(as: 'restricted', history: true)]
    public bool $restrictedOnly = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWindowDays(): void
    {
        $this->resetPage();
    }

    public function updatingRestrictedOnly(): void
    {
        $this->resetPage();
    }

    protected function cutoffDate()
    {
        return match ($this->windowDays) {
            'expired' => now()->startOfDay(),
            '60' => now()->addDays(60)->endOfDay(),
            '90' => now()->addDays(90)->endOfDay(),
            default => now()->addDays(30)->endOfDay(),
        };
    }

    public function daysLeft(ProductBatch $batch): int
    {
        return (int) now()->startOfDay()->diffInDays($batch->expiration_date, false);
    }

    public function render()
    {
        $cutoff = $this->cutoffDate();

        $baseQuery = ProductBatch::query()
            ->where('stock_quantity', '>', 0)
            ->where('expiration_date', '<=', $cutoff)
            ->when($this->restrictedOnly, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('is_restricted', true)))
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$this->search}%")));

        $expiredCount = (clone $baseQuery)->where('expiration_date', '<', now())->count();
        $expiredValue = (float) (clone $baseQuery)->where('expiration_date', '<', now())
            ->get()->sum(fn (ProductBatch $b) => (float) $b->stock_quantity * (float) ($b->purchase_price ?? 0));

        $soonCount = (clone $baseQuery)->where('expiration_date', '>=', now())->count();
        $soonValue = (float) (clone $baseQuery)->where('expiration_date', '>=', now())
            ->get()->sum(fn (ProductBatch $b) => (float) $b->stock_quantity * (float) ($b->purchase_price ?? 0));

        $batches = (clone $baseQuery)
            ->with('product.category')
            ->orderBy('expiration_date')
            ->paginate(10);

        return view('livewire.reports.expirations', [
            'batches' => $batches,
            'expiredCount' => $expiredCount,
            'expiredValue' => $expiredValue,
            'soonCount' => $soonCount,
            'soonValue' => $soonValue,
        ]);
    }
}
