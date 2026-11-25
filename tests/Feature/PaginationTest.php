<?php

use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

function seedProducts(int $count): void
{
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);

    foreach (range(1, $count) as $i) {
        Product::create([
            'category_id' => $category->id, 'sku' => sprintf('SKU-%04d', $i), 'name' => sprintf('Product %02d', $i),
            'unit' => 'kg', 'purchase_price' => 1000, 'selling_price' => 1500,
        ]);
    }
}

test('lists use the custom pagination view', function () {
    seedProducts(25);

    Livewire::test(Index::class)
        ->assertSeeHtml('class="pg"')
        ->assertSeeHtml('aria-current="page"')
        ->assertDontSee('Showing');
});

test('pagination buttons change the page', function () {
    seedProducts(25);

    Livewire::test(Index::class)
        ->call('gotoPage', 2)
        ->assertSee('Product 11')
        ->call('previousPage')
        ->assertSee('Product 01');
});

test('no pagination is rendered when everything fits on one page', function () {
    seedProducts(3);

    Livewire::test(Index::class)->assertDontSeeHtml('class="pg"');
});
