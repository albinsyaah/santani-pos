<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\PendingCart;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Point of sale')]
class Terminal extends Component
{
    public string $search = '';

    /** @var array<int, array{product_id:int, name:string, unit:string, unit_price:float, quantity:int, available:int}> */
    public array $cart = [];

    public ?int $customerId = null;

    public string $paymentMethod = 'cash';

    public string $discount = '0';

    public string $tax = '0';

    public string $paidAmount = '';

    public string $error = '';

    public string $success = '';

    public function mount(): void
    {
        $resumeId = request()->integer('resume');

        if (! $resumeId) {
            return;
        }

        $pending = PendingCart::find($resumeId);

        if (! $pending) {
            return;
        }

        $this->cart = $pending->cart_data;
        $this->customerId = $pending->customer_id;
        $this->paymentMethod = $pending->payment_method;
        $this->discount = (string) $pending->discount;
        $this->tax = (string) $pending->tax;

        $pending->delete();

        $this->success = __('Held cart resumed.');
    }

    public function holdCart(): void
    {
        if (empty($this->cart)) {
            $this->error = __('Add at least one product to the cart first.');

            return;
        }

        PendingCart::create([
            'user_id' => User::first()?->id,
            'customer_id' => $this->customerId,
            'note' => collect($this->cart)->pluck('name')->take(2)->implode(', ').(count($this->cart) > 2 ? ', …' : ''),
            'cart_data' => $this->cart,
            'payment_method' => $this->paymentMethod,
            'discount' => (float) ($this->discount ?: 0),
            'tax' => (float) ($this->tax ?: 0),
        ]);

        $this->reset(['cart', 'customerId', 'discount', 'tax', 'paidAmount']);
        $this->paymentMethod = 'cash';
        $this->discount = '0';
        $this->tax = '0';
        $this->error = '';
        $this->success = __('Cart held. Resume it anytime from Pending Cart.');
        unset($this->products);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $available = $product->total_stock;

        if ($available <= 0) {
            $this->error = __(':name is out of stock.', ['name' => $product->name]);

            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] >= $available) {
                $this->error = __('Only :qty :unit of :name available.', ['qty' => $available, 'unit' => $product->unit, 'name' => $product->name]);

                return;
            }
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'unit_price' => (float) $product->selling_price,
                'quantity' => 1,
                'available' => $available,
            ];
        }

        $this->error = '';
    }

    public function incrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['quantity'] >= $this->cart[$productId]['available']) {
            $this->error = __('Not enough stock available for that quantity.');

            return;
        }

        $this->cart[$productId]['quantity']++;
    }

    public function decrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['quantity']--;

        if ($this->cart[$productId]['quantity'] <= 0) {
            unset($this->cart[$productId]);
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->limit(24)
            ->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::orderBy('name')->limit(200)->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(fn ($line) => $line['unit_price'] * $line['quantity']);
    }

    #[Computed]
    public function grandTotal(): float
    {
        $discount = (float) ($this->discount ?: 0);
        $tax = (float) ($this->tax ?: 0);

        return max(0, $this->subtotal - $discount + $tax);
    }

    #[Computed]
    public function changeAmount(): float
    {
        $paid = (float) ($this->paidAmount ?: 0);

        return max(0, $paid - $this->grandTotal);
    }

    public function checkout(): void
    {
        $this->error = '';

        if (empty($this->cart)) {
            $this->error = __('Add at least one product to the cart first.');

            return;
        }

        $paid = (float) ($this->paidAmount ?: 0);
        $grandTotal = $this->grandTotal;
        $customer = $this->customerId ? Customer::find($this->customerId) : null;

        if ($paid < $grandTotal && ! $customer) {
            $this->error = __('Select a farmer/customer to record a sale as credit (unpaid balance).');

            return;
        }

        $unpaidBalance = max(0, $grandTotal - $paid);

        if ($customer && $unpaidBalance > 0 && ! $customer->canBorrow($unpaidBalance)) {
            $this->error = __(':name does not have enough available credit for this unpaid balance.', ['name' => $customer->name]);

            return;
        }

        $cashier = User::first();

        try {
            DB::transaction(function () use ($customer, $paid, $grandTotal, $unpaidBalance, $cashier) {
                $paymentStatus = $unpaidBalance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

                $sale = Sale::create([
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Sale::whereDate('created_at', now())->count() + 1), 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customer?->id,
                    'user_id' => $cashier?->id,
                    'subtotal' => $this->subtotal,
                    'discount' => (float) ($this->discount ?: 0),
                    'tax' => (float) ($this->tax ?: 0),
                    'grand_total' => $grandTotal,
                    'paid_amount' => min($paid, $grandTotal),
                    'change_amount' => $this->changeAmount,
                    'payment_method' => $this->paymentMethod,
                    'payment_status' => $paymentStatus,
                    'due_date' => $unpaidBalance > 0 ? now()->addDays(30) : null,
                ]);

                foreach ($this->cart as $line) {
                    $remaining = $line['quantity'];

                    $batches = ProductBatch::fefoAvailable()
                        ->where('product_id', $line['product_id'])
                        ->lockForUpdate()
                        ->get();

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $take = min($remaining, $batch->stock_quantity);

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $line['product_id'],
                            'batch_id' => $batch->id,
                            'unit_price' => $line['unit_price'],
                            'quantity' => $take,
                            'discount' => 0,
                            'subtotal' => $take * $line['unit_price'],
                        ]);

                        $batch->decrement('stock_quantity', $take);
                        $remaining -= $take;
                    }

                    // Fallback: not enough batch stock tracked (data inconsistency) — record remainder without a batch.
                    if ($remaining > 0) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $line['product_id'],
                            'batch_id' => null,
                            'unit_price' => $line['unit_price'],
                            'quantity' => $remaining,
                            'discount' => 0,
                            'subtotal' => $remaining * $line['unit_price'],
                        ]);
                    }
                }

                if ($customer && $unpaidBalance > 0) {
                    $customer->increment('current_debt', $unpaidBalance);
                }

                $this->success = __('Sale :invoice completed.', ['invoice' => $sale->invoice_number]);
            });
        } catch (\Throwable $e) {
            $this->error = __('Checkout failed: :message', ['message' => $e->getMessage()]);

            return;
        }

        $this->reset(['cart', 'customerId', 'discount', 'tax', 'paidAmount']);
        $this->paymentMethod = 'cash';
        $this->discount = '0';
        $this->tax = '0';
        unset($this->products);
    }

    public function render()
    {
        return view('livewire.pos.terminal');
    }
}
