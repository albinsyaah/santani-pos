<?php

use App\Livewire\Purchases\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => test()->actingAs(User::factory()->create()));

function purchaseWithStock(int $received, int $remaining): array
{
    $category = Category::create(['name' => 'Fertilizer', 'slug' => 'fertilizer']);
    $product = Product::create([
        'category_id' => $category->id, 'sku' => 'SKU-0001', 'name' => 'NPK', 'unit' => 'kg',
        'purchase_price' => 10000, 'selling_price' => 12500,
    ]);
    $batch = ProductBatch::create([
        'product_id' => $product->id, 'batch_number' => 'B-1', 'stock_quantity' => $remaining,
        'purchase_price' => 10000, 'selling_price' => 12500,
    ]);
    $purchase = Purchase::create([
        'purchase_number' => 'PUR-1', 'supplier_id' => Supplier::create(['name' => 'Sup'])->id,
        'user_id' => auth()->id(), 'total_amount' => $received * 10000, 'paid_amount' => 0, 'payment_status' => 'pending',
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id, 'product_id' => $product->id, 'batch_id' => $batch->id,
        'unit_cost' => 10000, 'quantity' => $received, 'subtotal' => $received * 10000,
    ]);

    return [$purchase, $batch];
}

test('deleting a purchase through the modal removes it and its stock', function () {
    [$purchase, $batch] = purchaseWithStock(10, 14);

    Livewire::test(Index::class)
        ->call('askDelete', $purchase->id)
        ->assertSet('deletingId', $purchase->id)
        ->call('deleteConfirmed')
        ->assertSet('deletingId', null);

    expect(Purchase::count())->toBe(0)
        ->and(PurchaseItem::count())->toBe(0)
        ->and($batch->fresh()->stock_quantity)->toBe(4);
});

test('a purchase whose stock was already used cannot be deleted', function () {
    [$purchase, $batch] = purchaseWithStock(10, 6);

    Livewire::test(Index::class)
        ->call('askDelete', $purchase->id)
        ->call('deleteConfirmed');

    expect(Purchase::count())->toBe(1)
        ->and($batch->fresh()->stock_quantity)->toBe(6);
});

test('cancelling the modal keeps the purchase', function () {
    [$purchase] = purchaseWithStock(10, 10);

    Livewire::test(Index::class)->call('askDelete', $purchase->id)->call('cancelDelete')->assertSet('deletingId', null);

    expect(Purchase::count())->toBe(1);
});
