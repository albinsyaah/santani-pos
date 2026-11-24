<?php

use App\Livewire\Purchases\Create;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

function purchaseProduct(): Product
{
    User::factory()->create();
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

test('only product and quantity are asked for on each row', function () {
    purchaseProduct();

    Livewire::test(Create::class)
        ->assertDontSee('Batch number')
        ->assertDontSee('Unit cost')
        ->assertDontSee('Selling price')
        ->assertDontSee('Manufactured')
        ->assertDontSee('Expires');
});

test('receiving stock uses catalog prices and an auto batch number', function () {
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
        ->and((float) $batch->purchase_price)->toBe(10000.0)
        ->and((float) $batch->selling_price)->toBe(12500.0)
        ->and($batch->expiration_date)->toBeNull()
        ->and((float) $purchase->total_amount)->toBe(300000.0)
        ->and($purchase->items)->toHaveCount(1);
});

test('product and quantity are required', function () {
    purchaseProduct();

    Livewire::test(Create::class)
        ->call('save')
        ->assertHasErrors(['supplierId', 'lines.0.product_id', 'lines.0.quantity']);
});
