<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Add Current Stock')]
class Opening extends Component
{
    /**
     * Stock lines being recorded.
     *
     * @var array<int, array{product_id:?int, search:string, batch_number:string, quantity:string, manufactured_date:string, expiration_date:string}>
     */
    public array $lines = [];

    public string $error = '';

    public string $flash = '';

    public function mount(): void
    {
        $this->addLine();
    }

    /** @return array<string, mixed> */
    protected function blankLine(): array
    {
        return [
            'product_id' => null,
            'search' => '',
            'batch_number' => ProductBatch::generateNumber(count($this->lines)),
            'quantity' => '',
            'manufactured_date' => '',
            'expiration_date' => '',
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = $this->blankLine();
        $this->error = '';
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);

        if (empty($this->lines)) {
            $this->addLine();
        }
    }

    /** Pick a product from the search results for the given row. */
    public function selectProduct(int $index, int $productId): void
    {
        if (! isset($this->lines[$index]) || ! Product::whereKey($productId)->exists()) {
            return;
        }

        $this->lines[$index]['product_id'] = $productId;
        $this->lines[$index]['search'] = '';
    }

    public function clearProduct(int $index): void
    {
        if (isset($this->lines[$index])) {
            $this->lines[$index]['product_id'] = null;
        }
    }

    /** Products matching a row's search text (name, SKU, barcode or active ingredient). */
    public function searchProducts(string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        return Product::query()
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhere('active_ingredient', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    /** Products already chosen in the rows, keyed by id, for display. */
    #[Computed]
    public function selectedProducts()
    {
        $ids = collect($this->lines)->pluck('product_id')->filter()->unique();

        return Product::whereIn('id', $ids)->get()->keyBy('id');
    }

    #[Computed]
    public function totalUnits(): int
    {
        return (int) collect($this->lines)->sum(fn ($line) => (int) ($line['quantity'] ?: 0));
    }

    public function save(): void
    {
        $this->error = '';
        $this->flash = '';

        // A cleared batch number is filled in automatically instead of failing validation.
        $blankCount = 0;
        foreach ($this->lines as $i => $line) {
            if (trim((string) ($line['batch_number'] ?? '')) === '') {
                $this->lines[$i]['batch_number'] = ProductBatch::generateNumber($blankCount++);
            }
        }

        $validated = $this->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.batch_number' => ['required', 'string', 'max:100'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.manufactured_date' => ['nullable', 'date'],
            // Existing inventory may already be close to (or past) its date,
            // so unlike incoming deliveries there is no "after:today" rule.
            'lines.*.expiration_date' => ['nullable', 'date'],
        ], [], [
            'lines.*.product_id' => __('product'),
            'lines.*.batch_number' => __('batch number'),
            'lines.*.quantity' => __('quantity'),
            'lines.*.manufactured_date' => __('manufactured date'),
            'lines.*.expiration_date' => __('expiration date'),
        ]);

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['lines'] as $line) {
                    $product = Product::findOrFail($line['product_id']);

                    // Same product + batch number tops up the existing batch.
                    $batch = ProductBatch::firstOrNew([
                        'product_id' => $product->id,
                        'batch_number' => $line['batch_number'],
                    ]);

                    // Prices are not asked for here: new batches take the catalog
                    // prices, existing batches keep theirs.
                    $batch->purchase_price ??= $product->purchase_price;
                    $batch->selling_price ??= $product->selling_price;
                    $batch->manufactured_date = ($line['manufactured_date'] ?? '') ?: null;
                    $batch->expiration_date = ($line['expiration_date'] ?? '') ?: null;
                    $batch->stock_quantity = (int) $batch->stock_quantity + (int) $line['quantity'];
                    $batch->save();
                }
            });
        } catch (\Throwable $e) {
            $this->error = __('Could not save the stock: :message', ['message' => $e->getMessage()]);

            return;
        }

        $this->flash = __(':count batch(es) added to inventory.', ['count' => count($validated['lines'])]);
        $this->lines = [];
        $this->addLine();
    }

    public function render()
    {
        return view('livewire.stock.opening');
    }
}
