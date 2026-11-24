<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Incoming Stock Entry')]
class Create extends Component
{
    public ?int $supplierId = null;

    public string $purchaseNumber = '';

    public string $paidAmount = '';

    public string $note = '';

    /**
     * Stock lines being received.
     *
     * @var array<int, array{product_id:?int, search:string, batch_number:string, quantity:string, unit_cost:string, selling_price:string}>
     */
    public array $lines = [];

    public string $error = '';

    public function mount(): void
    {
        $this->purchaseNumber = $this->nextPurchaseNumber();
        $this->addLine();
    }

    /** Build the next sequential purchase reference, e.g. PO-20260917-0001. */
    protected function nextPurchaseNumber(): string
    {
        $sequence = Purchase::whereDate('created_at', now())->count() + 1;

        return 'PO-'.now()->format('Ymd').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /** @return array<string, mixed> */
    protected function blankLine(): array
    {
        return [
            'product_id' => null,
            'search' => '',
            // Not shown on the form: every delivery line gets its own generated batch number.
            'batch_number' => ProductBatch::generateNumber(count($this->lines)),
            'quantity' => '',
            'unit_cost' => '',
            'selling_price' => '',
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

        $product = Product::find($productId);

        $this->lines[$index]['product_id'] = $productId;
        $this->lines[$index]['search'] = '';
        // Start from the catalog prices; change them if the invoice differs.
        $this->lines[$index]['unit_cost'] = (string) (float) $product->purchase_price;
        $this->lines[$index]['selling_price'] = (string) (float) $product->selling_price;
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

    /** Products already chosen in the rows, keyed by id, for display and costing. */
    #[Computed]
    public function selectedProducts()
    {
        $ids = collect($this->lines)->pluck('product_id')->filter()->unique();

        return Product::whereIn('id', $ids)->get()->keyBy('id');
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::orderBy('name')->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->lines)->sum(
            fn ($line) => (float) ($line['quantity'] ?: 0) * (float) ($line['unit_cost'] ?: 0)
        );
    }

    #[Computed]
    public function totalUnits(): int
    {
        return (int) collect($this->lines)->sum(fn ($line) => (int) ($line['quantity'] ?: 0));
    }

    #[Computed]
    public function balanceDue(): float
    {
        return max(0, $this->subtotal - (float) ($this->paidAmount ?: 0));
    }

    public function save(): void
    {
        $this->error = '';

        // A cleared batch number is filled in automatically instead of failing validation.
        $blankCount = 0;
        foreach ($this->lines as $i => $line) {
            if (trim((string) ($line['batch_number'] ?? '')) === '') {
                $this->lines[$i]['batch_number'] = ProductBatch::generateNumber($blankCount++);
            }
        }

        $validated = $this->validate([
            'supplierId' => ['required', 'exists:suppliers,id'],
            'purchaseNumber' => ['required', 'string', 'max:100', 'unique:purchases,purchase_number'],
            'paidAmount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.batch_number' => ['required', 'string', 'max:100'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'lines.*.selling_price' => ['required', 'numeric', 'min:0'],
        ], [], [
            'supplierId' => __('supplier'),
            'purchaseNumber' => __('purchase number'),
            'paidAmount' => __('paid amount'),
            'lines.*.product_id' => __('product'),
            'lines.*.batch_number' => __('batch number'),
            'lines.*.quantity' => __('quantity'),
            'lines.*.unit_cost' => __('purchase price'),
            'lines.*.selling_price' => __('selling price'),
        ]);

        $total = $this->subtotal;
        $paid = min((float) (($validated['paidAmount'] ?? '') ?: 0), $total);
        $receiver = User::first();

        try {
            $purchase = DB::transaction(function () use ($validated, $total, $paid, $receiver) {
                $purchase = Purchase::create([
                    'purchase_number' => $validated['purchaseNumber'],
                    'supplier_id' => $validated['supplierId'],
                    'user_id' => $receiver?->id,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                    'note' => ($validated['note'] ?? '') ?: null,
                ]);

                $products = Product::whereIn('id', collect($validated['lines'])->pluck('product_id'))->get()->keyBy('id');

                foreach ($validated['lines'] as $line) {
                    $product = $products[$line['product_id']];
                    $quantity = (int) $line['quantity'];
                    $unitCost = (float) $line['unit_cost'];
                    $sellingPrice = (float) $line['selling_price'];

                    // Re-use the batch when the same batch number arrives again for
                    // the same product; otherwise start a fresh batch record.
                    $batch = ProductBatch::firstOrNew([
                        'product_id' => $line['product_id'],
                        'batch_number' => $line['batch_number'],
                    ]);

                    // Manufactured/expiry dates are not asked for here, so a batch
                    // that already has them keeps them.
                    $batch->purchase_price = $unitCost;
                    $batch->selling_price = $sellingPrice;
                    $batch->stock_quantity = (int) $batch->stock_quantity + $quantity;
                    $batch->save();

                    // A price changed on this page becomes the product's real price.
                    if ((float) $product->purchase_price !== $unitCost || (float) $product->selling_price !== $sellingPrice) {
                        $product->update([
                            'purchase_price' => $unitCost,
                            'selling_price' => $sellingPrice,
                        ]);
                    }

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $line['product_id'],
                        'batch_id' => $batch->id,
                        'unit_cost' => $unitCost,
                        'quantity' => $quantity,
                        'subtotal' => $quantity * $unitCost,
                    ]);
                }

                return $purchase;
            });
        } catch (\Throwable $e) {
            $this->error = __('Could not save the stock entry: :message', ['message' => $e->getMessage()]);

            return;
        }

        session()->flash('flash', __('Stock entry :number recorded. :count batch(es) added to inventory.', [
            'number' => $purchase->purchase_number,
            'count' => count($validated['lines']),
        ]));

        $this->redirectRoute('purchases.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.create');
    }
}
