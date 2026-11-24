<?php

use App\Livewire\Sales\Tally;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

function tallyProduct(int $stock = 0): Product
{
    User::factory()->create();
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);

    $product = Product::create([
        'category_id' => $category->id, 'sku' => 'SKU-0001', 'name' => 'NPK 16-16-16', 'unit' => 'kg',
        'purchase_price' => 10000, 'selling_price' => 12500,
    ]);

    if ($stock > 0) {
        ProductBatch::create([
            'product_id' => $product->id, 'batch_number' => 'B-1', 'stock_quantity' => $stock,
            'purchase_price' => 10000, 'selling_price' => 12500, 'expiration_date' => now()->addYear(),
        ]);
    }

    return $product;
}

test('the tally page renders with today as the default date', function () {
    $this->get(route('sales.tally'))->assertOk()->assertSee('Sales Tally');

    Livewire::test(Tally::class)->assertSet('date', now()->format('Y-m-d'));
});

test('a tally creates a cash sale and deducts stock', function () {
    $product = tallyProduct(20);

    Livewire::test(Tally::class)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '8')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::with('items')->first();

    expect((float) $sale->grand_total)->toBe(100000.0)
        ->and($sale->payment_method)->toBe('cash')
        ->and($sale->payment_status)->toBe('paid')
        ->and($sale->notes)->toBe('Manual tally')
        ->and($sale->items->sum('quantity'))->toBe(8)
        ->and($product->fresh()->total_stock)->toBe(12);
});

test('a past date backdates the sale', function () {
    $product = tallyProduct(5);
    $date = now()->subDays(2)->format('Y-m-d');

    Livewire::test(Tally::class)
        ->set('date', $date)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '1')
        ->call('save')
        ->assertHasNoErrors();

    expect(Sale::first()->created_at->format('Y-m-d'))->toBe($date)
        ->and(Sale::first()->invoice_number)->toStartWith('INV-'.now()->subDays(2)->format('Ymd').'-');
});

test('selling more than the recorded stock still saves the sale', function () {
    $product = tallyProduct(3);

    Livewire::test(Tally::class)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '5')
        ->call('save')
        ->assertHasNoErrors();

    $items = Sale::first()->items;

    expect($items->sum('quantity'))->toBe(5)
        ->and($items->whereNull('batch_id')->sum('quantity'))->toBe(2)
        ->and(ProductBatch::first()->stock_quantity)->toBe(0);
});

test('the same product on two rows becomes one sale line', function () {
    $product = tallyProduct(10);

    Livewire::test(Tally::class)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '2')
        ->call('addLine')
        ->call('selectProduct', 1, $product->id)
        ->set('lines.1.quantity', '3')
        ->call('save')
        ->assertHasNoErrors();

    expect(Sale::first()->items)->toHaveCount(1)
        ->and(Sale::first()->items->first()->quantity)->toBe(5);
});

test('validation rejects missing fields and future dates', function () {
    tallyProduct();

    Livewire::test(Tally::class)
        ->set('date', now()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['date', 'lines.0.product_id', 'lines.0.quantity']);

    expect(Sale::count())->toBe(0);
});
