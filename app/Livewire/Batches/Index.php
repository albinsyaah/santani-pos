<?php

namespace App\Livewire\Batches;

use App\Models\Category;
use App\Models\ProductBatch;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Batches & Expiration Dates')]
class Index extends Component
{
    use WithPagination;

    /** Batches expiring within this many days are flagged as "expiring soon". */
    public const SOON_WINDOW_DAYS = 90;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'category', history: true)]
    public ?int $categoryId = null;

    /** all | fresh | soon | expired | depleted */
    #[Url(as: 'shelf', history: true)]
    public string $shelfStatus = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    // Batch form state
    public string $batchNumber = '';

    public string $stockQuantity = '';

    public string $purchasePrice = '';

    public string $sellingPrice = '';

    public string $manufacturedDate = '';

    public string $expirationDate = '';

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public string $flash = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingShelfStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'shelfStatus']);
        $this->resetPage();
    }

    /**
     * Batches narrowed by product search and category.
     * The shelf-life filter is applied separately so the summary cards can
     * stay a stable overview while the table drills into one status.
     */
    protected function baseQuery()
    {
        return ProductBatch::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('batch_number', 'like', "%{$this->search}%")
                        ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('sku', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->categoryId, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('category_id', $this->categoryId)));
    }

    protected function applyShelfFilter($query)
    {
        $soonCutoff = now()->addDays(self::SOON_WINDOW_DAYS)->endOfDay();

        return match ($this->shelfStatus) {
            'expired' => $query->where('expiration_date', '<', now()->startOfDay()),
            'soon' => $query->where('stock_quantity', '>', 0)
                ->where('expiration_date', '>=', now()->startOfDay())
                ->where('expiration_date', '<=', $soonCutoff),
            'fresh' => $query->where('stock_quantity', '>', 0)
                ->where('expiration_date', '>', $soonCutoff),
            'depleted' => $query->where('stock_quantity', '<=', 0),
            default => $query,
        };
    }

    /** Days until expiry — negative once the batch is past its date. */
    public function daysLeft(ProductBatch $batch): int
    {
        return (int) now()->startOfDay()->diffInDays($batch->expiration_date, false);
    }

    /** @return array{count:int, units:int, soon:int, expired:int, value:float} */
    #[Computed]
    public function summary(): array
    {
        $soonCutoff = now()->addDays(self::SOON_WINDOW_DAYS)->endOfDay();

        $batches = $this->baseQuery()->get(['stock_quantity', 'purchase_price', 'expiration_date']);
        $inStock = $batches->filter(fn ($b) => (int) $b->stock_quantity > 0);

        return [
            'count' => $batches->count(),
            'units' => (int) $inStock->sum('stock_quantity'),
            'soon' => $inStock->filter(fn ($b) => $b->expiration_date >= now()->startOfDay() && $b->expiration_date <= $soonCutoff)->count(),
            'expired' => $inStock->filter(fn ($b) => $b->expiration_date < now()->startOfDay())->count(),
            'value' => (float) $inStock->sum(fn ($b) => (int) $b->stock_quantity * (float) ($b->purchase_price ?? 0)),
        ];
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function openEditModal(int $batchId): void
    {
        $batch = ProductBatch::findOrFail($batchId);

        $this->editingId = $batch->id;
        $this->batchNumber = $batch->batch_number;
        $this->stockQuantity = (string) $batch->stock_quantity;
        $this->purchasePrice = $batch->purchase_price !== null ? (string) (float) $batch->purchase_price : '';
        $this->sellingPrice = $batch->selling_price !== null ? (string) (float) $batch->selling_price : '';
        $this->manufacturedDate = $batch->manufactured_date?->format('Y-m-d') ?? '';
        $this->expirationDate = $batch->expiration_date?->format('Y-m-d') ?? '';

        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        if (! $this->editingId) {
            return;
        }

        $validated = $this->validate([
            'batchNumber' => ['required', 'string', 'max:100'],
            'stockQuantity' => ['required', 'integer', 'min:0'],
            'purchasePrice' => ['nullable', 'numeric', 'min:0'],
            'sellingPrice' => ['nullable', 'numeric', 'min:0'],
            'manufacturedDate' => ['nullable', 'date'],
            // Corrections may legitimately set a past date, so no "after:today" here —
            // unlike stock entry, this screen exists partly to fix bad data.
            'expirationDate' => ['required', 'date'],
        ], [], [
            'batchNumber' => __('batch number'),
            'stockQuantity' => __('stock quantity'),
            'purchasePrice' => __('purchase price'),
            'sellingPrice' => __('selling price'),
            'manufacturedDate' => __('manufactured date'),
            'expirationDate' => __('expiration date'),
        ]);

        $batch = ProductBatch::findOrFail($this->editingId);

        $batch->update([
            'batch_number' => $validated['batchNumber'],
            'stock_quantity' => (int) $validated['stockQuantity'],
            'purchase_price' => $validated['purchasePrice'] !== '' ? $validated['purchasePrice'] : null,
            'selling_price' => $validated['sellingPrice'] !== '' ? $validated['sellingPrice'] : null,
            'manufactured_date' => $validated['manufacturedDate'] ?: null,
            'expiration_date' => $validated['expirationDate'],
        ]);

        $this->flash = __('Batch :number was updated.', ['number' => $batch->batch_number]);
        $this->closeFormModal();
    }

    public function askDelete(int $batchId): void
    {
        $batch = ProductBatch::with('product')->findOrFail($batchId);
        $this->deletingId = $batch->id;
        $this->deletingLabel = $batch->batch_number.' — '.($batch->product?->name ?? '');
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

        $batch = ProductBatch::find($this->deletingId);

        // A batch that has been sold or received is part of the audit trail.
        if ($batch && ($batch->saleItems()->exists() || $batch->purchaseItems()->exists())) {
            $this->flash = __('Batch :number is linked to sales or purchases and cannot be deleted.', ['number' => $batch->batch_number]);
            $this->cancelDelete();

            return;
        }

        if ($batch) {
            $number = $batch->batch_number;
            $batch->delete();
            $this->flash = __('Batch :number was removed.', ['number' => $number]);
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $batches = $this->applyShelfFilter($this->baseQuery())
            ->with('product.category')
            ->orderBy('expiration_date')
            ->paginate(10);

        return view('livewire.batches.index', ['batches' => $batches]);
    }
}
