<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Manual sales tally: lets staff key in what was sold (product + quantity + date)
 * when nobody was free to run the cashier. It creates a normal cash sale and
 * deducts stock, so reports and inventory stay correct.
 */
#[Layout('layouts.app')]
#[Title('Sales Tally')]
class Tally extends Component
{
    public const NOTE = 'Manual tally';

    public string $date = '';

    /** @var array<int, array{product_id:?int, search:string, quantity:string}> */
    public array $lines = [];

    public string $error = '';

    public string $flash = '';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->addLine();
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => null, 'search' => '', 'quantity' => ''];
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

    /** Products already chosen in the rows, keyed by id. */
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

    #[Computed]
    public function totalValue(): float
    {
        return collect($this->lines)->sum(function ($line) {
            $product = $line['product_id'] ? $this->selectedProducts->get($line['product_id']) : null;

            return $product ? (int) ($line['quantity'] ?: 0) * (float) $product->selling_price : 0;
        });
    }

    /** What has already been tallied by hand for the chosen date, per product. */
    #[Computed]
    public function recap()
    {
        if (! $this->date || ! strtotime($this->date)) {
            return collect();
        }

        return SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->where('notes', self::NOTE)->whereDate('created_at', $this->date))
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($items) => [
                'name' => $items->first()->product?->name ?? '—',
                'unit' => $items->first()->product?->unit,
                'quantity' => (int) $items->sum('quantity'),
                'amount' => (float) $items->sum('subtotal'),
            ])
            ->sortBy('name')
            ->values();
    }

    public function save(): void
    {
        $this->error = '';
        $this->flash = '';

        $validated = $this->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ], [], [
            'date' => __('date'),
            'lines.*.product_id' => __('product'),
            'lines.*.quantity' => __('quantity'),
        ]);

        $cashier = auth()->user();

        if (! $cashier) {
            $this->error = __('No user account exists to record the sale under.');

            return;
        }

        // The same product keyed in twice becomes a single sale line.
        $items = collect($validated['lines'])
            ->groupBy('product_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        $soldAt = Carbon::parse($validated['date'])->setTimeFrom(now());
        $invoice = '';

        try {
            DB::transaction(function () use ($items, $soldAt, $cashier, &$invoice) {
                $products = Product::whereIn('id', $items->keys())->get()->keyBy('id');
                $grandTotal = $items->sum(fn ($qty, $productId) => $qty * (float) $products[$productId]->selling_price);

                $invoice = $this->nextInvoiceNumber($soldAt);

                $sale = (new Sale)->forceFill([
                    'invoice_number' => $invoice,
                    'customer_id' => null,
                    'user_id' => $cashier->id,
                    'subtotal' => $grandTotal,
                    'discount' => 0,
                    'tax' => 0,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $grandTotal,
                    'change_amount' => 0,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'due_date' => null,
                    'notes' => self::NOTE,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);
                $sale->save();

                foreach ($items as $productId => $quantity) {
                    $price = (float) $products[$productId]->selling_price;
                    $remaining = $quantity;

                    $batches = ProductBatch::fefoAvailable()
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->get();

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $take = min($remaining, $batch->stock_quantity);

                        $this->createItem($sale, $productId, $batch->id, $price, $take, $soldAt);
                        $batch->decrement('stock_quantity', $take);
                        $remaining -= $take;
                    }

                    // More sold than the system has in stock: keep the sale, without a batch.
                    if ($remaining > 0) {
                        $this->createItem($sale, $productId, null, $price, $remaining, $soldAt);
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->error = __('Could not save the tally: :message', ['message' => $e->getMessage()]);

            return;
        }

        $this->flash = __('Tally saved as :invoice.', ['invoice' => $invoice]);
        $this->lines = [];
        $this->addLine();
    }

    protected function createItem(Sale $sale, int $productId, ?int $batchId, float $price, int $quantity, Carbon $at): void
    {
        (new SaleItem)->forceFill([
            'sale_id' => $sale->id,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'unit_price' => $price,
            'quantity' => $quantity,
            'discount' => 0,
            'subtotal' => $quantity * $price,
            'created_at' => $at,
            'updated_at' => $at,
        ])->save();
    }

    protected function nextInvoiceNumber(Carbon $date): string
    {
        $prefix = 'INV-'.$date->format('Ymd').'-';

        $last = Sale::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->pluck('invoice_number')
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.sales.tally');
    }
}
