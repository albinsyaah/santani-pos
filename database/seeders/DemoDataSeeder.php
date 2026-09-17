<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Roles & staff -------------------------------------------------
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);
        Role::firstOrCreate(['name' => 'Warehouse']);

        $owner = User::firstOrCreate(
            ['email' => 'owner@agropos.test'],
            [
                'role_id' => $ownerRole->id,
                'name' => 'Siti Rahayu',
                'password' => 'password',
                'phone' => '0812-3456-7890',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@agropos.test'],
            [
                'role_id' => $cashierRole->id,
                'name' => 'Budi Santoso',
                'password' => 'password',
                'phone' => '0813-2233-4455',
                'status' => 'active',
            ]
        );

        // ---- Categories ------------------------------------------------------
        $categories = collect([
            ['name' => 'NPK Fertilizers', 'description' => 'Balanced N-P-K blended fertilizers'],
            ['name' => 'Organic Fertilizers', 'description' => 'Compost and organic-based nutrients'],
            ['name' => 'Herbicides', 'description' => 'Weed control chemicals'],
            ['name' => 'Pesticides', 'description' => 'Insect and pest control chemicals'],
            ['name' => 'Fungicides', 'description' => 'Fungal disease control'],
            ['name' => 'Seeds', 'description' => 'Crop and vegetable seeds'],
        ])->map(fn ($c) => Category::firstOrCreate(['name' => $c['name']], [
            'slug' => str($c['name'])->slug(),
            'description' => $c['description'],
        ]));

        // ---- Suppliers ---------------------------------------------------
        $suppliers = collect([
            ['name' => 'PT Pupuk Nusantara', 'contact_person' => 'Andi Wijaya', 'phone' => '021-5550101'],
            ['name' => 'CV Agro Kimia Jaya', 'contact_person' => 'Rina Kusuma', 'phone' => '021-5550202'],
            ['name' => 'Tani Makmur Distributor', 'contact_person' => 'Dedi Hartono', 'phone' => '021-5550303'],
        ])->map(fn ($s) => Supplier::firstOrCreate(['name' => $s['name']], [
            'contact_person' => $s['contact_person'],
            'phone' => $s['phone'],
            'address' => 'Kawasan Industri, Karawang, Jawa Barat',
        ]));

        // ---- Products ------------------------------------------------------
        $catalog = [
            ['name' => 'NPK 16-16-16 Fertilizer 50kg', 'category' => 'NPK Fertilizers', 'unit' => 'sack', 'buy' => 385000, 'sell' => 450000, 'min' => 15, 'restricted' => false, 'ai' => null],
            ['name' => 'NPK Mutiara 25kg', 'category' => 'NPK Fertilizers', 'unit' => 'sack', 'buy' => 210000, 'sell' => 255000, 'min' => 10, 'restricted' => false, 'ai' => null],
            ['name' => 'Urea Prill 50kg', 'category' => 'NPK Fertilizers', 'unit' => 'sack', 'buy' => 290000, 'sell' => 335000, 'min' => 20, 'restricted' => false, 'ai' => null],
            ['name' => 'Organic Compost Plus 40kg', 'category' => 'Organic Fertilizers', 'unit' => 'sack', 'buy' => 95000, 'sell' => 130000, 'min' => 12, 'restricted' => false, 'ai' => null],
            ['name' => 'Bio-Fertilizer Liquid 5L', 'category' => 'Organic Fertilizers', 'unit' => 'liter', 'buy' => 60000, 'sell' => 85000, 'min' => 8, 'restricted' => false, 'ai' => null],
            ['name' => 'Roundup Glyphosate 480SL 1L', 'category' => 'Herbicides', 'unit' => 'bottle', 'buy' => 68000, 'sell' => 95000, 'min' => 10, 'restricted' => true, 'ai' => 'Glyphosate 480 g/L'],
            ['name' => 'Paraquat Herbicide 1L', 'category' => 'Herbicides', 'unit' => 'bottle', 'buy' => 72000, 'sell' => 99000, 'min' => 8, 'restricted' => true, 'ai' => 'Paraquat dichloride 276 g/L'],
            ['name' => 'Decis Insecticide 500ml', 'category' => 'Pesticides', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 79000, 'min' => 10, 'restricted' => true, 'ai' => 'Deltamethrin 25 g/L'],
            ['name' => 'Curacron Insecticide 500ml', 'category' => 'Pesticides', 'unit' => 'bottle', 'buy' => 58000, 'sell' => 82000, 'min' => 10, 'restricted' => true, 'ai' => 'Profenofos 500 g/L'],
            ['name' => 'Antracol Fungicide 500g', 'category' => 'Fungicides', 'unit' => 'pcs', 'buy' => 38000, 'sell' => 55000, 'min' => 15, 'restricted' => false, 'ai' => 'Propineb 70%'],
            ['name' => 'Dithane M-45 Fungicide 1kg', 'category' => 'Fungicides', 'unit' => 'pcs', 'buy' => 65000, 'sell' => 92000, 'min' => 10, 'restricted' => false, 'ai' => 'Mancozeb 80%'],
            ['name' => 'Hybrid Rice Seed 5kg', 'category' => 'Seeds', 'unit' => 'sack', 'buy' => 120000, 'sell' => 155000, 'min' => 6, 'restricted' => false, 'ai' => null],
            ['name' => 'Sweet Corn Seed 1kg', 'category' => 'Seeds', 'unit' => 'pcs', 'buy' => 85000, 'sell' => 115000, 'min' => 6, 'restricted' => false, 'ai' => null],
        ];

        $products = collect();

        foreach ($catalog as $i => $row) {
            $category = $categories->firstWhere('name', $row['category']);

            $product = Product::firstOrCreate(
                ['sku' => 'SKU-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'category_id' => $category->id,
                    'name' => $row['name'],
                    'active_ingredient' => $row['ai'],
                    'unit' => $row['unit'],
                    'min_stock_level' => $row['min'],
                    'purchase_price' => $row['buy'],
                    'selling_price' => $row['sell'],
                    'is_restricted' => $row['restricted'],
                ]
            );

            $products->push($product);

            // Two batches per product: a fresh one and one closer to expiry, so FEFO has something to do.
            if ($product->batches()->count() === 0) {
                ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'B'.now()->format('ym').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'A',
                    'purchase_price' => $row['buy'],
                    'selling_price' => $row['sell'],
                    'stock_quantity' => random_int(20, 80),
                    'manufactured_date' => now()->subMonths(6),
                    'expiration_date' => now()->addDays(random_int(20, 90)),
                ]);

                ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'B'.now()->format('ym').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'B',
                    'purchase_price' => $row['buy'],
                    'selling_price' => $row['sell'],
                    'stock_quantity' => random_int(15, 60),
                    'manufactured_date' => now()->subMonths(2),
                    'expiration_date' => now()->addMonths(random_int(8, 18)),
                ]);
            }
        }

        // Intentionally push a couple of products low so the dashboard's low-stock card has content.
        $products->take(2)->each(function (Product $product) {
            $product->batches()->update(['stock_quantity' => random_int(0, 4)]);
        });

        // ---- Customers -------------------------------------------------------
        $customers = collect([
            ['name' => 'Pak Slamet Riyadi', 'group' => 'Kelompok Tani Subur Makmur', 'limit' => 3000000],
            ['name' => 'Bu Wartini', 'group' => 'Kelompok Tani Sumber Rejeki', 'limit' => 2000000],
            ['name' => 'Pak Joko Widodo', 'group' => 'Kelompok Tani Mekar Jaya', 'limit' => 5000000],
            ['name' => 'Pak Ahmad Fauzi', 'group' => null, 'limit' => 1000000],
            ['name' => 'Bu Siti Aminah', 'group' => 'Kelompok Tani Sido Makmur', 'limit' => 2500000],
        ])->map(fn ($c) => Customer::firstOrCreate(['name' => $c['name']], [
            'phone' => '08'.random_int(10, 99).'-'.random_int(1000, 9999).'-'.random_int(1000, 9999),
            'farmer_group' => $c['group'],
            'address' => 'Desa Sukamaju, Karawang, Jawa Barat',
            'credit_limit' => $c['limit'],
            'current_debt' => 0,
        ]));

        // ---- A purchase per supplier (stock intake record) --------------------
        foreach ($suppliers as $i => $supplier) {
            if (Purchase::where('supplier_id', $supplier->id)->exists()) {
                continue;
            }

            $lines = $products->random(3);
            $total = 0;

            $purchase = Purchase::create([
                'purchase_number' => 'PO-'.now()->subDays(10 + $i)->format('Ymd').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'user_id' => $owner->id,
                'total_amount' => 0,
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
            ]);
            $purchase->created_at = now()->subDays(10 + $i);
            $purchase->save();

            foreach ($lines as $product) {
                $qty = random_int(30, 100);
                $subtotal = $qty * (float) $product->purchase_price;
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'batch_id' => $product->batches()->first()?->id,
                    'unit_cost' => $product->purchase_price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ]);
            }

            $paid = $i === 0 ? $total : ($i === 1 ? $total * 0.5 : 0);

            $purchase->update([
                'total_amount' => $total,
                'paid_amount' => $paid,
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
            ]);
        }

        // ---- Sales across the last 14 days, so the dashboard chart has shape ---
        $paymentMethods = ['cash', 'cash', 'cash', 'qris', 'transfer', 'credit'];
        $invoiceSeq = 1;

        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $salesToday = random_int(1, 6);

            for ($s = 0; $s < $salesToday; $s++) {
                $lineCount = random_int(1, 4);
                $lines = $products->random(min($lineCount, $products->count()));
                $subtotal = 0;
                $saleLines = [];

                foreach ($lines as $product) {
                    $qty = random_int(1, 5);
                    $lineSubtotal = $qty * (float) $product->selling_price;
                    $subtotal += $lineSubtotal;
                    $saleLines[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineSubtotal];
                }

                $discount = random_int(0, 1) ? round($subtotal * 0.02) : 0;
                $grandTotal = $subtotal - $discount;
                $method = $paymentMethods[array_rand($paymentMethods)];
                $useCustomer = $method === 'credit' || random_int(0, 3) === 0;
                $customer = $useCustomer ? $customers->random() : null;
                $paid = $method === 'credit' ? round($grandTotal * (random_int(0, 1) ? 0.5 : 0)) : $grandTotal;
                $unpaid = max(0, $grandTotal - $paid);

                $sale = Sale::create([
                    'invoice_number' => 'INV-'.$date->format('Ymd').'-'.str_pad((string) $invoiceSeq++, 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customer?->id,
                    'user_id' => $owner->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => 0,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paid,
                    'change_amount' => max(0, $paid - $grandTotal),
                    'payment_method' => $method,
                    'payment_status' => $unpaid <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                    'due_date' => $unpaid > 0 ? $date->clone()->addDays(30) : null,
                ]);
                $sale->created_at = $date->clone()->setTime(random_int(8, 17), random_int(0, 59));
                $sale->save();

                foreach ($saleLines as $line) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $line['product']->id,
                        'batch_id' => $line['product']->batches()->first()?->id,
                        'unit_price' => $line['product']->selling_price,
                        'quantity' => $line['qty'],
                        'discount' => 0,
                        'subtotal' => $line['subtotal'],
                    ]);
                }

                if ($customer && $unpaid > 0) {
                    $customer->increment('current_debt', $unpaid);
                }
            }
        }

        // ---- A couple of held carts, so the Pending Cart page has content --------
        if (\App\Models\PendingCart::count() === 0) {
            $heldLines = $products->random(2)->values()->map(fn (Product $p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'unit' => $p->unit,
                'unit_price' => (float) $p->selling_price,
                'quantity' => random_int(1, 3),
                'available' => $p->total_stock,
            ])->keyBy('product_id')->all();

            \App\Models\PendingCart::create([
                'user_id' => $owner->id,
                'customer_id' => $customers->random()->id,
                'note' => collect($heldLines)->pluck('name')->implode(', '),
                'cart_data' => $heldLines,
                'payment_method' => 'cash',
                'discount' => 0,
                'tax' => 0,
            ]);

            $singleLine = $products->random(1)->values()->map(fn (Product $p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'unit' => $p->unit,
                'unit_price' => (float) $p->selling_price,
                'quantity' => 1,
                'available' => $p->total_stock,
            ])->keyBy('product_id')->all();

            \App\Models\PendingCart::create([
                'user_id' => $owner->id,
                'customer_id' => null,
                'note' => collect($singleLine)->pluck('name')->implode(', '),
                'cart_data' => $singleLine,
                'payment_method' => 'cash',
                'discount' => 0,
                'tax' => 0,
            ]);
        }
    }
}
