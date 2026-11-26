<?php

use App\Livewire\Reports\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

test('revenue by category totals sale items per category with shares', function () {
    $a = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);
    $b = Category::create(['name' => 'Seeds', 'slug' => 'seeds']);
    $pa = Product::create(['category_id' => $a->id, 'sku' => 'A-1', 'name' => 'A', 'unit' => 'kg', 'purchase_price' => 1, 'selling_price' => 2]);
    Product::create(['category_id' => $b->id, 'sku' => 'B-1', 'name' => 'B', 'unit' => 'kg', 'purchase_price' => 1, 'selling_price' => 2]);

    $sale = (new Sale)->forceFill([
        'invoice_number' => 'INV-1', 'user_id' => auth()->id(), 'subtotal' => 300, 'grand_total' => 300,
        'paid_amount' => 300, 'payment_method' => 'cash', 'payment_status' => 'paid',
    ]);
    $sale->save();
    SaleItem::create(['sale_id' => $sale->id, 'product_id' => $pa->id, 'unit_price' => 100, 'quantity' => 3, 'subtotal' => 300]);

    $rows = Livewire::test(Index::class)->assertOk()->get('salesByCategory');

    expect($rows[0]['name'])->toBe('Fertilizer')
        ->and($rows[0]['revenue'])->toBe(300.0)
        ->and($rows[0]['units'])->toBe(3)
        ->and((int) $rows[0]['share'])->toBe(100)
        ->and($rows[1]['name'])->toBe('Seeds')
        ->and($rows[1]['revenue'])->toBe(0.0);
});
