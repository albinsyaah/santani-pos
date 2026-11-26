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
            ['username' => 'owner'],
            [
                'role_id' => $ownerRole->id,
                'name' => 'Admin',
                'password' => 'password',
                'phone' => '0812-3456-7890',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['username' => 'cashier'],
            [
                'role_id' => $cashierRole->id,
                'name' => 'Kasir',
                'password' => 'password',
                'phone' => '0813-2233-4455',
                'status' => 'active',
            ]
        );

        // ---- Categories ------------------------------------------------------
        $categories = collect([
            ['name' => 'Other', 'description' => '-'],
            ['name' => 'Subdisi', 'description' => '-'],
        ])->map(fn ($c) => Category::firstOrCreate(['name' => $c['name']], [
            'slug' => str($c['name'])->slug(),
            'description' => $c['description'],
        ]));

        // ---- Suppliers ---------------------------------------------------
        $suppliers = collect([
            ['name' => 'RIIBRI', 'contact_person' => '', 'phone' => ''],
            ['name' => 'Tunas Tani Makmur', 'contact_person' => '', 'phone' => ''],
            ['name' => 'Pupuk Subsidi', 'contact_person' => '', 'phone' => ''],
        ])->map(fn ($s) => Supplier::firstOrCreate(['name' => $s['name']], [
            'contact_person' => $s['contact_person'],
            'phone' => $s['phone'],
            'address' => '',
        ]));

        // ---- Products ------------------------------------------------------
        $catalog = [
            ['name' => 'Amistartop 100 ml (10)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Amistartop 250 ml (24)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Abacel 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 245000, 'sell' => 250000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Abacel 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Abacel 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 100000, 'sell' => 105000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Armor', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 195000, 'sell' => 200000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Antracol 250 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Antracol 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Antracol 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ally WDG', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ally Plus (Board Plus)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 10000, 'sell' => 15000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Antero 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ayuma 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Aplaud 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Amate 50 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Amate 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 110000, 'sell' => 115000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Avidor 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 28000, 'sell' => 33000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Anakon Mas 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Aheto 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 23000, 'sell' => 28000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Acemain 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 190000, 'sell' => 195000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Arraw 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Aki Doton', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Aki Chota Bah', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 210000, 'sell' => 215000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Amora 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Anggur Oyong Eules Tavi', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Azatin 500 SC 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 125000, 'sell' => 130000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ares 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Arivo 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Astertrin 200 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Arivo 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ashiap 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 90000, 'sell' => 95000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Astonish 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bactocyn 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bafis 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bamba Ijo', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 3000, 'sell' => 8000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bulir Buah 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Booster Padi 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Baricin 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 185000, 'sell' => 190000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bentan 100 gr Biru 60 WP', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Barozeb 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 95000, 'sell' => 100000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Basazinon 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bayam Amarin 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bayam Kartika 50 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 13000, 'sell' => 18000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Broad-Plus', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 10000, 'sell' => 15000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'BASF-Quat 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Blewah Blaster F1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bamba Ijo (4800)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 3000, 'sell' => 8000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bestox 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Blast Gome 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bell Top 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 225000, 'sell' => 230000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bayam Amarin 500 gr (2)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bayam 50 gr (Eceran)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 10000, 'sell' => 15000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bima 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bentan 100 gr 80 WP', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bintang Amakan 300 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bestox 80 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Benhur 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Biggest Tablet', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Bestox 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 88000, 'sell' => 93000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Covestro 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 195000, 'sell' => 200000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Madun', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Gelora Besar', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Prentul', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Rawit Genie 2 gr Bintang Asia', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 7000, 'sell' => 12000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Calsium Kayla 25 gr (10)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 10000, 'sell' => 15000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Alexa 10 gr (26)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 27000, 'sell' => 32000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Charger/Cas Tangki Elektrik', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Cabe Rawit Genie 10 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Charger Multi', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Camix 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Crumble 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Crumble 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 125000, 'sell' => 130000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Centazole 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 110000, 'sell' => 115000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Centazole 80 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'CBA-6 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Comet 900 gram', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Confidor 100 ml Cair', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Decis 50 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Decis 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Decis 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 135000, 'sell' => 140000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Debest 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Dapat 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 90000, 'sell' => 95000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Dehis 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Decis 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Draxon 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Dipomax 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Darmabas 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'De-Star 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Dora Calsium/Sehat 25 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Extratin 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Extratin 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'EM-4 Kuning (Pertanian)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'EM-4 Coklat (Ternak)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'EM-4 Pink (Ikan)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'EM-4 Biru (Closet)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'EX-Gro 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Explore 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 120000, 'sell' => 125000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Emacel 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 83000, 'sell' => 88000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fastac 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fello RG 06', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fello RG 02', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fujiwan 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Furadan / Bks', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Furadan / Box', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 355000, 'sell' => 360000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Floxs 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 215000, 'sell' => 220000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fostin 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fitozeb 80 WP 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Fertera 2 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gobest 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gandasil D 100 gr / Hijau', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 7000, 'sell' => 12000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gandasil D 500 gr / Hijau', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gandasil B 100 gr / Merah', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 7000, 'sell' => 12000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gandasil B 500 gr / Merah', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gempur 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gramoxon 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Great-Til 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gib-Grow Panen', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 3000, 'sell' => 8000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gib-Grow Saset Biasa', 'category' => 'Other', 'unit' => 'bottle', 'buy' => -500, 'sell' => 4500, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Golden Top 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 95000, 'sell' => 100000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Grain 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Gramoxone 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Horor 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hoky 500 ml Cair', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Heko 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hexa / Heksa 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 32000, 'sell' => 37000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hexagold', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hantu Jimmy 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hantu Jimmy 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Homine 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 100000, 'sell' => 105000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Homine 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 210000, 'sell' => 215000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Haga 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hesia 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hitrea 5 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Hitrea Eceran 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 4000, 'sell' => 9000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'HS-Stik 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Imidatrin 200 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Imidatrin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ichiphon / Etrel', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Intare 50 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Imidasal 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Inchipro 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Jagung Manis Maestro 100 gr (17) (BG)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Jagung Manis Maestro 250 gr (DA)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Joker 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Joker 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 190000, 'sell' => 195000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Jofat 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Josmex 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kacang Katrina', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 22000, 'sell' => 27000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Krati S/S DB', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Krati 28K Elektrik', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 13000, 'sell' => 18000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kura Terbang', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kangkung HD', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ketave 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Klopindo 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kangkung Maestro 100 gr (BA)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 8000, 'sell' => 13000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kangkung Maestro 1 kg (EH)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kamikaze 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kamikaze 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 135000, 'sell' => 140000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'KCL HS 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Klenseet 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Klenseet 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'KCL Super Cair 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kejora 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kejora 1 Lt (68)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kresnadati / Bks', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kresnadati / Box', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 315000, 'sell' => 320000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kho Merah', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kho Putih 1 kg (72)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kisan', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 11000, 'sell' => 16000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kiss Up 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kacang Jaliteng Panjang 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kacang Panjang Jaliteng 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kozima 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 145000, 'sell' => 150000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kovin Plus Tikus', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 0, 'sell' => 5000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kangkung Rajawali 1 kg (Pak Maim)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kakute', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Kangkung Intisari 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Limpo 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Lugem 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Logram', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 4000, 'sell' => 9000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Metro 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 195000, 'sell' => 200000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mastros 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 195000, 'sell' => 200000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Marshal 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 73000, 'sell' => 78000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mycinta 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Montaf 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'MKP Paktani (58)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Metsulindo 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 11000, 'sell' => 16000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Metindo 200 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Metindo 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 100000, 'sell' => 105000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Merxone', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mec King 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 185000, 'sell' => 190000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Meotrim 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Maradona 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Monkey 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Miltop 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 38000, 'sell' => 43000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Marshal SC 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Merbabu Daun 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 90000, 'sell' => 95000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Merbabu Buah 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 105000, 'sell' => 110000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mawson 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Magio 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 120000, 'sell' => 125000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Menxone 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mospilan 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Mospilan 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 90000, 'sell' => 95000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Oztrin 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Oztrin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Oyong Jaka F1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Patrol 10 ml (BE)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pentacarb 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Prevaton 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 130000, 'sell' => 135000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pounder 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pakar 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Petrokom', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 7000, 'sell' => 12000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Primafos 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Paseidon 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Paria Wirafi', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Packcoy Masbro', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 17000, 'sell' => 22000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Prevaton 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Paria Hokian F1 10 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 17000, 'sell' => 22000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pluton 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 125000, 'sell' => 130000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pluton 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 245000, 'sell' => 250000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pluton 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 375000, 'sell' => 380000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Pithon 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 135000, 'sell' => 140000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Primatine 450 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ricestar 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ricestar 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 140000, 'sell' => 145000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rizotin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Radar 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regent 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regent 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 165000, 'sell' => 170000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regent Tabur 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regent Saset (per saset/box)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 4000, 'sell' => 9000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regate 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 125000, 'sell' => 130000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Remifa 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Round Up 200 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rad Fast 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rotex 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 145000, 'sell' => 150000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rapid', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rotex 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rumba 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rumba 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 38000, 'sell' => 43000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rommaks 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rotonik 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rahwana 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 58000, 'sell' => 63000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rahwana 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 110000, 'sell' => 115000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Rumpas 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Regent 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 110000, 'sell' => 115000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Speed Extra Daun 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Speed Extra Buah 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sheltin 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 95000, 'sell' => 100000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Santat 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 190000, 'sell' => 195000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sheltin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Spuyer Lb.5', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Spuyer Lb.7', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 28000, 'sell' => 33000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Spuyer Lb.1 Lurus', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 8000, 'sell' => 13000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Spuyer Lb.1 Bongkok', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 11000, 'sell' => 16000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Selang Multi / Elektrik', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 23000, 'sell' => 28000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Spuyer Lb.4 Lurus Plastik', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 10000, 'sell' => 15000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik Teleskopik Swan', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Supramin 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik Gun Panjang 40 cm', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik Gun Pendek 30 cm', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik S/S DB', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 18000, 'sell' => 23000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik S/S DM', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 18000, 'sell' => 23000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Selang S/S DB Swan', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Stik Teleskopik DT Elektrik', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Super Deo Plus 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'SOH-1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starmex 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starban 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starban 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 76000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starban 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 175000, 'sell' => 180000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Super Boss 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sidabas 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sangit 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 17000, 'sell' => 22000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sangit 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starban 200 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Starban 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Super Calsie 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sati Gold', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 11000, 'sell' => 16000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Serendi', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sawi Dora 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sumo 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 95000, 'sell' => 100000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sumo 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Score 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 165000, 'sell' => 170000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Score 80 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 65000, 'sell' => 70000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Super Gib', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Star-Plus', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Sitiergi 250 ml (135)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 140000, 'sell' => 145000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Selang Multi Elektrik (2)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 15000, 'sell' => 20000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Skybati 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 80000, 'sell' => 85000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Skybati 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Trigger', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Top Bos Daun / Hijau', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 25000, 'sell' => 30000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Top Bos Buah / Merah', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tamabas 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Torbimol 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 215000, 'sell' => 220000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Triactive 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Trebon 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 100000, 'sell' => 105000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Termiban 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Topsin 200 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Topsin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Top Bintang', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Traversa 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 185000, 'sell' => 190000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ti-Gold', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 14000, 'sell' => 19000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tebas 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tebas 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tromkus Tikur 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Timun Balebat', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Traversa 25 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 60000, 'sell' => 65000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Timun Labata F1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 35000, 'sell' => 40000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Timun Suri (Tisuri F1)', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 37000, 'sell' => 42000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Terong Bulat Kenari 10 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 18000, 'sell' => 23000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Terong Ungu Bangko F1 gr 5', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Three Sefat 400 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 85000, 'sell' => 90000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tompas 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 28000, 'sell' => 33000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Three Sefat 1 kg', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 190000, 'sell' => 195000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tangki CBA Ultra 16 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 495000, 'sell' => 500000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Timun Benlebat 20 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tandem 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Top Gun 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 225000, 'sell' => 230000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Trimax 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Tatu\'vit B 500 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 30000, 'sell' => 35000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Toram 100 gr', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 33000, 'sell' => 38000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Trisula 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 75000, 'sell' => 80000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ultradap', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Vitamin B1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 20000, 'sell' => 25000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Vaksin', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Ventura 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 40000, 'sell' => 45000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Vasitrim 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 55000, 'sell' => 60000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Vidamex 1 Lt', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 180000, 'sell' => 185000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Vulgar 400 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 45000, 'sell' => 50000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Wulan Timun 97 F1', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Zole 100 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 50000, 'sell' => 55000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Zole 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 115000, 'sell' => 120000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Zomba 250 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 305000, 'sell' => 310000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Zetrin 500 ml', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 70000, 'sell' => 75000, 'min' => 1, 'restricted' => false, 'ai' => null],
            ['name' => 'Zetrin 1 Liter', 'category' => 'Other', 'unit' => 'bottle', 'buy' => 155000, 'sell' => 160000, 'min' => 1, 'restricted' => false, 'ai' => null],
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

            // One batch per product with fixed stock quantity and null dates
            if ($product->batches()->count() === 0) {
                ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'B'.now()->format('ym').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'A',
                    'purchase_price' => $row['buy'],
                    'selling_price' => $row['sell'],
                    'stock_quantity' => 5,
                    'manufactured_date' => null,
                    'expiration_date' => null,
                ]);
            }
        }


            // Intentionally push a couple of products low so the dashboard's low-stock card has content.
        // $products->take(2)->each(function (Product $product) {
        //     $product->batches()->update(['stock_quantity' => random_int(0, 4)]);
        // });

            // // ---- Customers -------------------------------------------------------
        // $customers = collect([
        //     ['name' => 'Pak Slamet Riyadi', 'group' => 'Kelompok Tani Subur Makmur', 'limit' => 3000000],
        //     ['name' => 'Bu Wartini', 'group' => 'Kelompok Tani Sumber Rejeki', 'limit' => 2000000],
        //     ['name' => 'Pak Joko Widodo', 'group' => 'Kelompok Tani Mekar Jaya', 'limit' => 5000000],
        //     ['name' => 'Pak Ahmad Fauzi', 'group' => null, 'limit' => 1000000],
        //     ['name' => 'Bu Siti Aminah', 'group' => 'Kelompok Tani Sido Makmur', 'limit' => 2500000],
        // ])->map(fn ($c) => Customer::firstOrCreate(['name' => $c['name']], [
        //     'phone' => '08'.random_int(10, 99).'-'.random_int(1000, 9999).'-'.random_int(1000, 9999),
        //     'farmer_group' => $c['group'],
        //     'address' => 'Desa Sukamaju, Karawang, Jawa Barat',
        //     'credit_limit' => $c['limit'],
        //     'current_debt' => 0,
        // ]));

            // ---- A purchase per supplier (stock intake record) --------------------
        // foreach ($suppliers as $i => $supplier) {
        //     if (Purchase::where('supplier_id', $supplier->id)->exists()) {
        //         continue;
        //     }

        //     $lines = $products->random(3);
        //     $total = 0;

        //     $purchase = Purchase::create([
        //         'purchase_number' => 'PO-'.now()->subDays(10 + $i)->format('Ymd').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
        //         'supplier_id' => $supplier->id,
        //         'user_id' => $owner->id,
        //         'total_amount' => 0,
        //         'paid_amount' => 0,
        //         'payment_status' => 'unpaid',
        //     ]);
        //     $purchase->created_at = now()->subDays(10 + $i);
        //     $purchase->save();

        //     foreach ($lines as $product) {
        //         $qty = random_int(30, 100);
        //         $subtotal = $qty * (float) $product->purchase_price;
        //         $total += $subtotal;

        //         PurchaseItem::create([
        //             'purchase_id' => $purchase->id,
        //             'product_id' => $product->id,
        //             'batch_id' => $product->batches()->first()?->id,
        //             'unit_cost' => $product->purchase_price,
        //             'quantity' => $qty,
        //             'subtotal' => $subtotal,
        //         ]);
        //     }

        //     $paid = $i === 0 ? $total : ($i === 1 ? $total * 0.5 : 0);

        //     $purchase->update([
        //         'total_amount' => $total,
        //         'paid_amount' => $paid,
        //         'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        //     ]);
        // }

            // ---- Sales across the last 14 days, so the dashboard chart has shape ---
        // $paymentMethods = ['cash', 'cash', 'cash'];
        // $invoiceSeq = 1;

        // for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
        //     $date = now()->subDays($daysAgo);
        //     $salesToday = random_int(1, 6);

        //     for ($s = 0; $s < $salesToday; $s++) {
        //         $lineCount = random_int(1, 4);
        //         $lines = $products->random(min($lineCount, $products->count()));
        //         $subtotal = 0;
        //         $saleLines = [];

        //         foreach ($lines as $product) {
        //             $qty = random_int(1, 5);
        //             $lineSubtotal = $qty * (float) $product->selling_price;
        //             $subtotal += $lineSubtotal;
        //             $saleLines[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineSubtotal];
        //         }

        //         $discount = random_int(0, 1) ? round($subtotal * 0.02) : 0;
        //         $grandTotal = $subtotal - $discount;
        //         $method = $paymentMethods[array_rand($paymentMethods)];
        //         $useCustomer = $method === 'credit' || random_int(0, 3) === 0;
        //         $customer = $useCustomer ? $customers->random() : null;
        //         $paid = $method === 'credit' ? round($grandTotal * (random_int(0, 1) ? 0.5 : 0)) : $grandTotal;
        //         $unpaid = max(0, $grandTotal - $paid);

        //         $sale = Sale::create([
        //             'invoice_number' => 'INV-'.$date->format('Ymd').'-'.str_pad((string) $invoiceSeq++, 4, '0', STR_PAD_LEFT),
        //             'customer_id' => $customer?->id,
        //             'user_id' => $owner->id,
        //             'subtotal' => $subtotal,
        //             'discount' => $discount,
        //             'tax' => 0,
        //             'grand_total' => $grandTotal,
        //             'paid_amount' => $paid,
        //             'change_amount' => max(0, $paid - $grandTotal),
        //             'payment_method' => $method,
        //             'payment_status' => $unpaid <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        //             'due_date' => $unpaid > 0 ? $date->clone()->addDays(30) : null,
        //         ]);
        //         $sale->created_at = $date->clone()->setTime(random_int(8, 17), random_int(0, 59));
        //         $sale->save();

        //         foreach ($saleLines as $line) {
        //             SaleItem::create([
        //                 'sale_id' => $sale->id,
        //                 'product_id' => $line['product']->id,
        //                 'batch_id' => $line['product']->batches()->first()?->id,
        //                 'unit_price' => $line['product']->selling_price,
        //                 'quantity' => $line['qty'],
        //                 'discount' => 0,
        //                 'subtotal' => $line['subtotal'],
        //             ]);
        //         }

        //         if ($customer && $unpaid > 0) {
        //             $customer->increment('current_debt', $unpaid);
        //         }
        //     }
        // }

            // ---- A couple of held carts, so the Pending Cart page has content --------
        // if (\App\Models\PendingCart::count() === 0) {
        //     $heldLines = $products->random(2)->values()->map(fn (Product $p) => [
        //         'product_id' => $p->id,
        //         'name' => $p->name,
        //         'unit' => $p->unit,
        //         'unit_price' => (float) $p->selling_price,
        //         'quantity' => random_int(1, 3),
        //         'available' => $p->total_stock,
        //     ])->keyBy('product_id')->all();

        //     \App\Models\PendingCart::create([
        //         'user_id' => $owner->id,
        //         'customer_id' => $customers->random()->id,
        //         'note' => collect($heldLines)->pluck('name')->implode(', '),
        //         'cart_data' => $heldLines,
        //         'payment_method' => 'cash',
        //         'discount' => 0,
        //         'tax' => 0,
        //     ]);

        //     $singleLine = $products->random(1)->values()->map(fn (Product $p) => [
        //         'product_id' => $p->id,
        //         'name' => $p->name,
        //         'unit' => $p->unit,
        //         'unit_price' => (float) $p->selling_price,
        //         'quantity' => 1,
        //         'available' => $p->total_stock,
        //     ])->keyBy('product_id')->all();

        //     \App\Models\PendingCart::create([
        //         'user_id' => $owner->id,
        //         'customer_id' => null,
        //         'note' => collect($singleLine)->pluck('name')->implode(', '),
        //         'cart_data' => $singleLine,
        //         'payment_method' => 'cash',
        //         'discount' => 0,
        //         'tax' => 0,
        //     ]);
        // }
    }
}
