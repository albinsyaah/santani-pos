<?php

use App\Livewire\Purchases\Create;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

function purchaseProduct(): Product
{
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);

    return Product::create([
        'category_id' => $category->id, 'sku' => 'SKU-0001', 'name' => 'NPK 16-16-16', 'unit' => 'kg',
        'purchase_price' => 10000, 'selling_price' => 12500,
    ]);
}

test('products can be searched and picked on the incoming stock page', function () {
    $product = purchaseProduct();

    Livewire::test(Create::class)
        ->set('lines.0.search', 'npk')
        ->assertSee('NPK 16-16-16')
        ->call('selectProduct', 0, $product->id)
        ->assertSet('lines.0.product_id', $product->id);
});

test('rows ask for prices but not batch number or dates', function () {
    purchaseProduct();

    Livewire::test(Create::class)
        ->assertSee('Purchase price')
        ->assertSee('Selling price')
        ->assertDontSee('Batch number')
        ->assertDontSee('Manufactured')
        ->assertDontSee('Expires');
});

test('picking a product prefills its catalog prices', function () {
    $product = purchaseProduct();

    Livewire::test(Create::class)
        ->call('selectProduct', 0, $product->id)
        ->assertSet('lines.0.unit_cost', '10000')
        ->assertSet('lines.0.selling_price', '12500');
});

test('receiving stock creates a batch with an auto number', function () {
    $product = purchaseProduct();
    $supplier = Supplier::create(['name' => 'PT Tani']);

    Livewire::test(Create::class)
        ->set('supplierId', $supplier->id)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '30')
        ->call('save')
        ->assertHasNoErrors();

    $batch = ProductBatch::first();
    $purchase = Purchase::with('items')->first();

    expect($batch->stock_quantity)->toBe(30)
        ->and($batch->batch_number)->toStartWith('B'.now()->format('ymd').'-')
        ->and($batch->expiration_date)->toBeNull()
        ->and((float) $purchase->total_amount)->toBe(300000.0)
        ->and($purchase->items)->toHaveCount(1);
});

test('changing prices on the page also changes the product prices', function () {
    $product = purchaseProduct();
    $supplier = Supplier::create(['name' => 'PT Tani']);

    Livewire::test(Create::class)
        ->set('supplierId', $supplier->id)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '10')
        ->set('lines.0.unit_cost', '11000')
        ->set('lines.0.selling_price', '14000')
        ->call('save')
        ->assertHasNoErrors();

    $product->refresh();
    $batch = ProductBatch::first();

    expect((float) $product->purchase_price)->toBe(11000.0)
        ->and((float) $product->selling_price)->toBe(14000.0)
        ->and((float) $batch->purchase_price)->toBe(11000.0)
        ->and((float) $batch->selling_price)->toBe(14000.0)
        ->and((float) Purchase::first()->total_amount)->toBe(110000.0);
});

test('product and quantity are required', function () {
    purchaseProduct();

    Livewire::test(Create::class)
        ->call('save')
        ->assertHasErrors(['supplierId', 'lines.0.product_id', 'lines.0.quantity']);
});

test('paid to supplier follows the total cost until edited', function () {
    $product = purchaseProduct();

    $component = Livewire::test(Create::class)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '10')
        ->assertSet('paidAmount', '100000')
        ->set('lines.0.quantity', '12')
        ->assertSet('paidAmount', '120000');

    $component->set('paidAmount', '50000')
        ->set('lines.0.quantity', '20')
        ->assertSet('paidAmount', '50000');
});

test('the default paid amount records the delivery as paid', function () {
    $product = purchaseProduct();
    $supplier = Supplier::create(['name' => 'PT Tani']);

    Livewire::test(Create::class)
        ->set('supplierId', $supplier->id)
        ->call('selectProduct', 0, $product->id)
        ->set('lines.0.quantity', '5')
        ->call('save')
        ->assertHasNoErrors();

    expect(Purchase::first()->payment_status)->toBe('paid')
        ->and((float) Purchase::first()->paid_amount)->toBe(50000.0);
});
