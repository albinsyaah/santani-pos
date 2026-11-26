<?php

use App\Livewire\Pos\Terminal;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

function posProduct(): Product
{
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);

    $product = Product::create([
        'category_id' => $category->id, 'sku' => 'SKU-0001', 'name' => 'NPK 16-16-16', 'unit' => 'kg',
        'purchase_price' => 10000, 'selling_price' => 12500,
    ]);

    ProductBatch::create([
        'product_id' => $product->id, 'batch_number' => 'B-1', 'stock_quantity' => 50,
        'purchase_price' => 10000, 'selling_price' => 12500, 'expiration_date' => now()->addYear(),
    ]);

    return $product;
}

test('amount paid follows the grand total until edited', function () {
    $product = posProduct();

    $component = Livewire::test(Terminal::class)
        ->call('addToCart', $product->id)
        ->assertSet('paidAmount', '12500')
        ->call('incrementQty', $product->id)
        ->assertSet('paidAmount', '25000')
        ->set('discount', '5000')
        ->assertSet('paidAmount', '20000');

    $component->set('paidAmount', '10000')
        ->call('incrementQty', $product->id)
        ->assertSet('paidAmount', '10000');
});

test('the default amount paid completes the sale without change', function () {
    $product = posProduct();

    Livewire::test(Terminal::class)
        ->call('addToCart', $product->id)
        ->call('checkout')
        ->assertSet('error', '')
        ->assertSet('paidAmount', '');

    expect(\App\Models\Sale::first()->payment_status)->toBe('paid');
});
