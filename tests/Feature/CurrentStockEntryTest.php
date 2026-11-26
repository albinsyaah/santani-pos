<?php

use App\Livewire\Stock\Opening;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

function makeStockProduct(): Product
{
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);

    return Product::create([
        'category_id' => $category->id,
        'sku' => 'SKU-001',
        'name' => 'NPK 16-16-16',
        'unit' => 'kg',
        'purchase_price' => 10000,
        'selling_price' => 12500,
    ]);
}

test('the current stock page renders', function () {
    $this->get(route('stock.opening'))->assertOk()->assertSee('Add Current Stock');
});

test('products can be searched by name or SKU and picked', function () {
    $product = makeStockProduct();

    $component = Livewire::test(Opening::class)
        ->set('lines.0.search', 'npk')
        ->assertSee('NPK 16-16-16')
        ->set('lines.0.search', 'SKU-001')
        ->assertSee('NPK 16-16-16')
        ->set('lines.0.search', 'zzz')
        ->assertSee('No products found.')
        ->call('selectProduct', 0, $product->id)
        ->assertSet('lines.0.product_id', $product->id)
        ->assertSet('lines.0.search', '');

    $component->call('clearProduct', 0)->assertSet('lines.0.product_id', null);
});

test('batch number and prices are not shown on the page', function () {
    makeStockProduct();

    Livewire::test(Opening::class)
        ->assertDontSee('Batch number')
        ->assertDontSee('Purchase price')
        ->assertDontSee('Selling price');
});

test('it records current stock as a new batch', function () {
    $product = makeStockProduct();

    Livewire::test(Opening::class)
        ->set('lines.0.product_id', $product->id)
        ->set('lines.0.batch_number', 'B-001')
        ->set('lines.0.quantity', '25')
        ->set('lines.0.expiration_date', now()->addYear()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    $batch = ProductBatch::first();

    expect($batch->product_id)->toBe($product->id)
        ->and($batch->stock_quantity)->toBe(25)
        ->and((float) $batch->purchase_price)->toBe(10000.0)
        ->and((float) $batch->selling_price)->toBe(12500.0);
});

test('the same batch number tops up the existing batch', function () {
    $product = makeStockProduct();
    $expires = now()->addYear()->format('Y-m-d');

    foreach ([10, 15] as $quantity) {
        Livewire::test(Opening::class)
            ->set('lines.0.product_id', $product->id)
            ->set('lines.0.batch_number', 'B-001')
            ->set('lines.0.quantity', (string) $quantity)
            ->set('lines.0.expiration_date', $expires)
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(ProductBatch::count())->toBe(1)
        ->and(ProductBatch::first()->stock_quantity)->toBe(25);
});

test('required fields are validated', function () {
    Livewire::test(Opening::class)
        ->call('save')
        ->assertHasErrors([
            'lines.0.product_id',
            'lines.0.quantity',
        ]);

    expect(ProductBatch::count())->toBe(0);
});

test('the batch number is auto-filled and a blank one is generated on save', function () {
    $product = makeStockProduct();
    $prefix = 'B'.now()->format('ymd').'-';

    $component = Livewire::test(Opening::class)
        ->assertSet('lines.0.batch_number', $prefix.'001');

    $component->call('addLine')->assertSet('lines.1.batch_number', $prefix.'002');

    $component->set('lines.0.product_id', $product->id)
        ->set('lines.0.batch_number', '')
        ->set('lines.0.quantity', '5')
        ->set('lines.0.expiration_date', now()->addYear()->format('Y-m-d'))
        ->call('removeLine', 1)
        ->call('save')
        ->assertHasNoErrors();

    expect(ProductBatch::first()->batch_number)->toBe($prefix.'001');

    Livewire::test(Opening::class)->assertSet('lines.0.batch_number', $prefix.'002');
});

test('manufactured and expiration dates are optional', function () {
    $product = makeStockProduct();

    Livewire::test(Opening::class)
        ->set('lines.0.product_id', $product->id)
        ->set('lines.0.quantity', '12')
        ->call('save')
        ->assertHasNoErrors();

    $batch = ProductBatch::first();

    expect($batch->manufactured_date)->toBeNull()
        ->and($batch->expiration_date)->toBeNull()
        ->and($product->fresh()->total_stock)->toBe(12)
        ->and(ProductBatch::fefoAvailable()->count())->toBe(1);
});

function stockBatch(Product $product, string $number, int $qty, ?string $expires): ProductBatch
{
    return ProductBatch::create([
        'product_id' => $product->id,
        'batch_number' => $number,
        'stock_quantity' => $qty,
        'purchase_price' => 10000,
        'selling_price' => 12500,
        'expiration_date' => $expires,
    ]);
}

test('reducing stock takes the earliest expiry first across batches', function () {
    $product = makeStockProduct();
    $late = stockBatch($product, 'LATE', 10, now()->addYear()->format('Y-m-d'));
    $early = stockBatch($product, 'EARLY', 5, now()->addMonth()->format('Y-m-d'));
    $none = stockBatch($product, 'NONE', 4, null);

    Livewire::test(Opening::class)
        ->call('setMode', 0, 'reduce')
        ->set('lines.0.product_id', $product->id)
        ->set('lines.0.quantity', '8')
        ->call('save')
        ->assertHasNoErrors();

    expect($early->fresh()->stock_quantity)->toBe(0)
        ->and($late->fresh()->stock_quantity)->toBe(7)
        ->and($none->fresh()->stock_quantity)->toBe(4);
});

test('reducing more than the stock on hand is rejected and changes nothing', function () {
    $product = makeStockProduct();
    $batch = stockBatch($product, 'B-1', 5, null);

    Livewire::test(Opening::class)
        ->call('setMode', 0, 'reduce')
        ->set('lines.0.product_id', $product->id)
        ->set('lines.0.quantity', '6')
        ->call('save')
        ->assertHasErrors(['lines.0.quantity']);

    expect($batch->fresh()->stock_quantity)->toBe(5);
});

test('add and reduce rows can be saved together', function () {
    $product = makeStockProduct();
    $batch = stockBatch($product, 'B-1', 10, null);

    Livewire::test(Opening::class)
        ->set('lines.0.product_id', $product->id)
        ->set('lines.0.quantity', '3')
        ->call('addLine')
        ->call('setMode', 1, 'reduce')
        ->set('lines.1.product_id', $product->id)
        ->set('lines.1.quantity', '12')
        ->call('save')
        ->assertHasNoErrors();

    expect((int) ProductBatch::where('product_id', $product->id)->sum('stock_quantity'))->toBe(1);
});
