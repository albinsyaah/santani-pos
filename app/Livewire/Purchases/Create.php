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
     * @var array<int, array{product_id:?int, batch_number:string, quantity:string, unit_cost:string, selling_price:string, manufactured_date:string, expiration_date:string}>
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
            'batch_number' => '',
            'quantity' => '',
            'unit_cost' => '',
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

    /**
     * Prefill cost/price from the product catalog when a product is picked,
     * so the operator only has to correct what actually differs on the invoice.
     */
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

        if (($this->lines[$index]['unit_cost'] ?? '') === '') {
            $this->lines[$index]['unit_cost'] = (string) (float) $product->purchase_price;
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
            'lines.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.manufactured_date' => ['nullable', 'date'],
            'lines.*.expiration_date' => ['required', 'date', 'after:today'],
        ], [], [
            'supplierId' => __('supplier'),
            'purchaseNumber' => __('purchase number'),
            'paidAmount' => __('paid amount'),
            'lines.*.product_id' => __('product'),
            'lines.*.batch_number' => __('batch number'),
            'lines.*.quantity' => __('quantity'),
            'lines.*.unit_cost' => __('unit cost'),
            'lines.*.selling_price' => __('selling price'),
            'lines.*.manufactured_date' => __('manufactured date'),
            'lines.*.expiration_date' => __('expiration date'),
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

                foreach ($validated['lines'] as $line) {
                    $quantity = (int) $line['quantity'];
                    $unitCost = (float) $line['unit_cost'];

                    // Re-use the batch when the same batch number arrives again for
                    // the same product; otherwise start a fresh batch record.
                    $batch = ProductBatch::firstOrNew([
                        'product_id' => $line['product_id'],
                        'batch_number' => $line['batch_number'],
                    ]);

                    $sellingPrice = $line['selling_price'] ?? '';

                    $batch->purchase_price = $unitCost;
                    $batch->selling_price = $sellingPrice !== '' && $sellingPrice !== null
                        ? (float) $sellingPrice
                        : ($batch->selling_price ?? Product::find($line['product_id'])?->selling_price);
                    $batch->manufactured_date = ($line['manufactured_date'] ?? '') ?: null;
                    $batch->expiration_date = $line['expiration_date'];
                    $batch->stock_quantity = (int) $batch->stock_quantity + $quantity;
                    $batch->save();

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
