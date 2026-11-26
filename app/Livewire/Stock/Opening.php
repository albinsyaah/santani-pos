<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
     * @var array<int, array{product_id:?int, mode:string, search:string, batch_number:string, quantity:string, manufactured_date:string, expiration_date:string}>
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
            // 'add' puts stock on the shelf, 'reduce' takes it off (damage, expiry, corrections).
            'mode' => 'add',
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

    public function setMode(int $index, string $mode): void
    {
        if (isset($this->lines[$index]) && in_array($mode, ['add', 'reduce'], true)) {
            $this->lines[$index]['mode'] = $mode;
            $this->resetErrorBag();
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

    /** Total units on hand per chosen product (all batches, expired ones included). */
    #[Computed]
    public function stockOnHand()
    {
        $ids = collect($this->lines)->pluck('product_id')->filter()->unique();

        return ProductBatch::whereIn('product_id', $ids)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(stock_quantity) as total')
            ->pluck('total', 'product_id');
    }

    #[Computed]
    public function totalAdded(): int
    {
        return (int) collect($this->lines)->where('mode', 'add')->sum(fn ($line) => (int) ($line['quantity'] ?: 0));
    }

    #[Computed]
    public function totalReduced(): int
    {
        return (int) collect($this->lines)->where('mode', 'reduce')->sum(fn ($line) => (int) ($line['quantity'] ?: 0));
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
            'lines.*.mode' => ['required', 'in:add,reduce'],
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
                foreach ($validated['lines'] as $index => $line) {
                    $product = Product::findOrFail($line['product_id']);

                    if ($line['mode'] === 'reduce') {
                        $this->reduceStock($index, $product, (int) $line['quantity']);

                        continue;
                    }

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
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->error = __('Could not save the stock: :message', ['message' => $e->getMessage()]);

            return;
        }

        $lines = collect($validated['lines']);

        $this->flash = __('Stock updated: :added row(s) added, :reduced row(s) reduced.', [
            'added' => $lines->where('mode', 'add')->count(),
            'reduced' => $lines->where('mode', 'reduce')->count(),
        ]);
        $this->lines = [];
        $this->addLine();
    }

    /**
     * Take stock off a product, earliest expiry first (batches without an expiry
     * date last). Expired batches are included so they can be written off.
     */
    protected function reduceStock(int $index, Product $product, int $quantity): void
    {
        $batches = ProductBatch::where('product_id', $product->id)
            ->where('stock_quantity', '>', 0)
            ->orderByRaw('CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiration_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $available = (int) $batches->sum('stock_quantity');

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                "lines.{$index}.quantity" => __('Only :available in stock.', ['available' => $available]),
            ]);
        }

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $batch->stock_quantity);
            $batch->decrement('stock_quantity', $take);
            $remaining -= $take;
        }
    }

    public function render()
    {
        return view('livewire.stock.opening');
    }
}
