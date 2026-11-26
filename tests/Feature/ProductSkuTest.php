<?php

use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

function skuTestCategory(): Category
{
    return Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);
}

test('the new product form opens with an auto-filled SKU', function () {
    Livewire::test(Index::class)
        ->call('openCreateModal')
        ->assertSet('sku', 'SKU-0001');
});

test('the auto-filled SKU continues from the highest existing one', function () {
    $category = skuTestCategory();

    foreach (['SKU-0007', 'CUSTOM-1'] as $sku) {
        Product::create([
            'category_id' => $category->id, 'sku' => $sku, 'name' => $sku, 'unit' => 'kg',
            'purchase_price' => 1, 'selling_price' => 2,
        ]);
    }

    Livewire::test(Index::class)
        ->call('openCreateModal')
        ->assertSet('sku', 'SKU-0008');
});

test('a blank SKU is generated on save', function () {
    $category = skuTestCategory();

    Livewire::test(Index::class)
        ->call('openCreateModal')
        ->set('name', 'Urea')
        ->set('sku', '')
        ->set('formCategoryId', $category->id)
        ->set('purchasePrice', '1000')
        ->set('sellingPrice', '1500')
        ->call('save')
        ->assertHasNoErrors();

    expect(Product::first()->sku)->toBe('SKU-0001');
});
