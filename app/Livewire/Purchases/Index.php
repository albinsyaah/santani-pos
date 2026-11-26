<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Purchase History')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'supplier', history: true)]
    public ?int $supplierId = null;

    #[Url(as: 'status', history: true)]
    public string $status = '';

    #[Url(as: 'from', history: true)]
    public string $dateFrom = '';

    #[Url(as: 'to', history: true)]
    public string $dateTo = '';

    /** Purchase currently expanded in the detail modal. */
    public ?int $viewingId = null;

    /** Purchase waiting for delete confirmation. */
    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSupplierId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'supplierId', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function viewPurchase(int $purchaseId): void
    {
        $this->viewingId = $purchaseId;
    }

    public function closeDetail(): void
    {
        $this->viewingId = null;
    }

    public function askDelete(int $purchaseId): void
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $this->deletingId = $purchase->id;
        $this->deletingLabel = $purchase->purchase_number;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingLabel = '';
    }

    /**
     * Delete the purchase and take its received quantities back out of stock.
     * Refused when part of that stock has already been sold or reduced.
     */
    public function deleteConfirmed(): void
    {
        $purchase = $this->deletingId ? Purchase::find($this->deletingId) : null;

        if (! $purchase) {
            $this->cancelDelete();

            return;
        }

        $number = $purchase->purchase_number;
        $blocked = false;

        DB::transaction(function () use ($purchase, &$blocked) {
            $received = $purchase->items()->whereNotNull('batch_id')->get()
                ->groupBy('batch_id')
                ->map(fn ($rows) => (int) $rows->sum('quantity'));

            $batches = ProductBatch::whereIn('id', $received->keys())->lockForUpdate()->get()->keyBy('id');

            foreach ($received as $batchId => $quantity) {
                if (($batches[$batchId]->stock_quantity ?? 0) < $quantity) {
                    $blocked = true;

                    return;
                }
            }

            foreach ($received as $batchId => $quantity) {
                $batches[$batchId]->decrement('stock_quantity', $quantity);
            }

            $purchase->delete();
        });

        session()->flash('flash', $blocked
            ? __(':number cannot be deleted because part of its stock was already sold or reduced.', ['number' => $number])
            : __(':number was deleted and its stock was removed.', ['number' => $number]));

        $this->cancelDelete();
        $this->resetPage();
    }

    /** Base query with every active filter applied — shared by the table and the totals. */
    protected function baseQuery()
    {
        return Purchase::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('purchase_number', 'like', "%{$this->search}%")
                        ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->supplierId, fn ($q) => $q->where('supplier_id', $this->supplierId))
            ->when($this->status, fn ($q) => $q->where('payment_status', $this->status))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::orderBy('name')->get();
    }

    /** @return array{count:int, total:float, paid:float, outstanding:float} */
    #[Computed]
    public function summary(): array
    {
        $row = $this->baseQuery()
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(paid_amount), 0) as paid')
            ->first();

        $total = (float) ($row->total ?? 0);
        $paid = (float) ($row->paid ?? 0);

        return [
            'count' => (int) ($row->invoice_count ?? 0),
            'total' => $total,
            'paid' => $paid,
            'outstanding' => max(0, $total - $paid),
        ];
    }

    #[Computed]
    public function viewingPurchase(): ?Purchase
    {
        if (! $this->viewingId) {
            return null;
        }

        return Purchase::with(['supplier', 'user', 'items.product', 'items.batch'])->find($this->viewingId);
    }

    public function render()
    {
        $purchases = $this->baseQuery()
            ->with('supplier')
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('livewire.purchases.index', ['purchases' => $purchases]);
    }
}
