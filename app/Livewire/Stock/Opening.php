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
     * @var array<int, array{product_id:?int, batch_number:string, quantity:string, purchase_price:string, selling_price:string, manufactured_date:string, expiration_date:string}>
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
            'batch_number' => ProductBatch::generateNumber(count($this->lines)),
            'quantity' => '',
            'purchase_price' => '',
            'selling_price' => '',
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

    /** Prefill prices from the product catalog when a product is picked. */
    public function updatedLines(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.product_id') || ! $value) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        $product = Product::find($value);

        if (! $product) {
            return;
        }

        if (($this->lines[$index]['purchase_price'] ?? '') === '') {
            $this->lines[$index]['purchase_price'] = (string) (float) $product->purchase_price;
        }

        if (($this->lines[$index]['selling_price'] ?? '') === '') {
            $this->lines[$index]['selling_price'] = (string) (float) $product->selling_price;
        }
    }

    #[Computed]
    public function products()
    {
        return Product::orderBy('name')->get();
    }

    #[Computed]
    public function totalUnits(): int
    {
        return (int) collect($this->lines)->sum(fn ($line) => (int) ($line['quantity'] ?: 0));
    }

    #[Computed]
    public function totalValue(): float
    {
        return collect($this->lines)->sum(
            fn ($line) => (float) ($line['quantity'] ?: 0) * (float) ($line['purchase_price'] ?: 0)
        );
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
            'lines.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.manufactured_date' => ['nullable', 'date'],
            // Existing inventory may already be close to (or past) its date,
            // so unlike incoming deliveries there is no "after:today" rule.
            'lines.*.expiration_date' => ['required', 'date'],
        ], [], [
            'lines.*.product_id' => __('product'),
            'lines.*.batch_number' => __('batch number'),
            'lines.*.quantity' => __('quantity'),
            'lines.*.purchase_price' => __('purchase price'),
            'lines.*.selling_price' => __('selling price'),
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

                    $purchasePrice = ($line['purchase_price'] ?? '') !== '' ? $line['purchase_price'] : null;
                    $sellingPrice = ($line['selling_price'] ?? '') !== '' ? $line['selling_price'] : null;

                    $batch->purchase_price = $purchasePrice ?? $batch->purchase_price ?? $product->purchase_price;
                    $batch->selling_price = $sellingPrice ?? $batch->selling_price ?? $product->selling_price;
                    $batch->manufactured_date = ($line['manufactured_date'] ?? '') ?: null;
                    $batch->expiration_date = $line['expiration_date'];
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
