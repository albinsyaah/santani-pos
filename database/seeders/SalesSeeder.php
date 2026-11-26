<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data penjualan 6-26 September 2026, ditranskrip dari foto buku catatan
 * penjualan toko (WhatsApp Image 2026-09-29 ... 9 foto), disesuaikan dengan
 * produk_santani.md.
 *
 *  - Nama produk di bawah SAMA PERSIS dengan nama di produk_santani.md.
 *  - Produk yang tidak ada di produk_santani.md (pupuk, benih, dll.) dibuat
 *    otomatis lewat $extras.
 *  - Harga satuan = harga yang tertulis di buku (bukan harga katalog).
 *  - Urea & Phonska dijual per kg (1 sak = 50 kg): 185.000/sak = 3.700/kg,
 *    189.000/sak = 3.780/kg, supaya qty tetap bilangan bulat.
 *  - Stok: tiap produk dibuatkan 1 batch SEED yang cukup untuk semua
 *    penjualan, sisa stok akhir = 40 (urea/phonska 2.000 kg).
 *
 * Jalankan:  php artisan db:seed --class=SalesSeeder
 * Aman dijalankan ulang (dilewati jika invoice SEED sudah ada).
 */
class SalesSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('sales')->where('invoice_number', 'like', 'INV-2026%-S%')->exists()) {
            $this->command?->warn('SalesSeeder: data penjualan seed sudah ada, dilewati.');
            return;
        }

        $catalog = [
        'Abacel 250 ml' => ['unit' => 'bottle', 'buy' => 245000, 'sell' => 250000],
        'Amistartop 100 ml (10)' => ['unit' => 'bottle', 'buy' => 105000, 'sell' => 110000],
        'Amistartop 250 ml (24)' => ['unit' => 'bottle', 'buy' => 105000, 'sell' => 110000],
        'Antracol 1 kg' => ['unit' => 'bottle', 'buy' => 85000, 'sell' => 90000],
        'Aplaud 400 gr' => ['unit' => 'bottle', 'buy' => 55000, 'sell' => 60000],
        'Ares 100 ml' => ['unit' => 'bottle', 'buy' => 25000, 'sell' => 30000],
        'Bafis 1 Lt' => ['unit' => 'bottle', 'buy' => 70000, 'sell' => 75000],
        'Bayam 50 gr (Eceran)' => ['unit' => 'bottle', 'buy' => 10000, 'sell' => 15000],
        'Bentan 100 gr 80 WP' => ['unit' => 'bottle', 'buy' => 70000, 'sell' => 75000],
        'Bestox 250 ml' => ['unit' => 'bottle', 'buy' => 50000, 'sell' => 55000],
        'Centazole 250 ml' => ['unit' => 'bottle', 'buy' => 110000, 'sell' => 115000],
        'Centazole 80 ml' => ['unit' => 'bottle', 'buy' => 45000, 'sell' => 50000],
        'Comet 900 gram' => ['unit' => 'bottle', 'buy' => 180000, 'sell' => 185000],
        'Confidor 100 ml Cair' => ['unit' => 'bottle', 'buy' => 65000, 'sell' => 70000],
        'De-Star 250 ml' => ['unit' => 'bottle', 'buy' => 65000, 'sell' => 70000],
        'Decis 100 ml' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Decis 250 ml' => ['unit' => 'bottle', 'buy' => 70000, 'sell' => 75000],
        'Decis 500 ml' => ['unit' => 'bottle', 'buy' => 135000, 'sell' => 140000],
        'Fostin 400 ml' => ['unit' => 'bottle', 'buy' => 70000, 'sell' => 75000],
        'Furadan / Bks' => ['unit' => 'bottle', 'buy' => 33000, 'sell' => 38000],
        'Gandasil B 500 gr / Merah' => ['unit' => 'bottle', 'buy' => 40000, 'sell' => 45000],
        'Gandasil D 100 gr / Hijau' => ['unit' => 'bottle', 'buy' => 7000, 'sell' => 12000],
        'Gandasil D 500 gr / Hijau' => ['unit' => 'bottle', 'buy' => 40000, 'sell' => 45000],
        'Gempur 1 Liter' => ['unit' => 'bottle', 'buy' => 70000, 'sell' => 75000],
        'Gib-Grow Panen' => ['unit' => 'bottle', 'buy' => 3000, 'sell' => 8000],
        'Gib-Grow Saset Biasa' => ['unit' => 'bottle', 'buy' => 3500, 'sell' => 4500],
        'Gramoxone 250 ml' => ['unit' => 'bottle', 'buy' => 25000, 'sell' => 30000],
        'Hoky 500 ml Cair' => ['unit' => 'bottle', 'buy' => 25000, 'sell' => 30000],
        'Imidatrin 200 ml' => ['unit' => 'bottle', 'buy' => 33000, 'sell' => 38000],
        'Imidatrin 500 ml' => ['unit' => 'bottle', 'buy' => 60000, 'sell' => 65000],
        'Joker 1 kg' => ['unit' => 'bottle', 'buy' => 190000, 'sell' => 195000],
        'Joker 400 gr' => ['unit' => 'bottle', 'buy' => 85000, 'sell' => 90000],
        'KCL HS 500 ml' => ['unit' => 'bottle', 'buy' => 15000, 'sell' => 20000],
        'KCL Super Cair 1 Lt' => ['unit' => 'bottle', 'buy' => 60000, 'sell' => 65000],
        'Kangkung HD' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Klenseet 400 ml' => ['unit' => 'bottle', 'buy' => 75000, 'sell' => 80000],
        'MKP Paktani (58)' => ['unit' => 'bottle', 'buy' => 60000, 'sell' => 65000],
        'Metsulindo 100 gr' => ['unit' => 'bottle', 'buy' => 11000, 'sell' => 16000],
        'Patrol 10 ml (BE)' => ['unit' => 'bottle', 'buy' => 15000, 'sell' => 20000],
        'Regent 100 ml' => ['unit' => 'bottle', 'buy' => 45000, 'sell' => 50000],
        'Ricestar 100 ml' => ['unit' => 'bottle', 'buy' => 60000, 'sell' => 65000],
        'Round Up 200 ml' => ['unit' => 'bottle', 'buy' => 25000, 'sell' => 30000],
        'Rumpas 100 ml' => ['unit' => 'bottle', 'buy' => 50000, 'sell' => 55000],
        'Score 250 ml' => ['unit' => 'bottle', 'buy' => 165000, 'sell' => 170000],
        'Sidabas 500 ml' => ['unit' => 'bottle', 'buy' => 33000, 'sell' => 38000],
        'Spuyer Lb.4 Lurus Plastik' => ['unit' => 'bottle', 'buy' => 10000, 'sell' => 15000],
        'Spuyer Lb.5' => ['unit' => 'bottle', 'buy' => 20000, 'sell' => 25000],
        'Starban 400 ml' => ['unit' => 'bottle', 'buy' => 76000, 'sell' => 80000],
        'Stik Teleskopik Swan' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Tandem 250 ml' => ['unit' => 'bottle', 'buy' => 155000, 'sell' => 160000],
        'Ti-Gold' => ['unit' => 'bottle', 'buy' => 14000, 'sell' => 19000],
        'Topsin 500 ml' => ['unit' => 'bottle', 'buy' => 75000, 'sell' => 80000],
        'Trisula 1 Lt' => ['unit' => 'bottle', 'buy' => 75000, 'sell' => 80000],
        'Ultradap' => ['unit' => 'bottle', 'buy' => 50000, 'sell' => 55000],
    ];

        $extras = [
        'Bintang' => ['unit' => 'bottle', 'buy' => 25000, 'sell' => 30000],
        'Booster' => ['unit' => 'bottle', 'buy' => 60000, 'sell' => 65000],
        'Emy' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Fastac 100 ml' => ['unit' => 'bottle', 'buy' => 35000, 'sell' => 40000],
        'Fonce' => ['unit' => 'bottle', 'buy' => 35000, 'sell' => 40000],
        'Galon (pestisida)' => ['unit' => 'bottle', 'buy' => 95000, 'sell' => 100000],
        'Gatiqa' => ['unit' => 'bottle', 'buy' => 15000, 'sell' => 20000],
        'Gibro Pell' => ['unit' => 'bottle', 'buy' => 22000, 'sell' => 27000],
        'Inpari (benih padi)' => ['unit' => 'pack', 'buy' => 105000, 'sell' => 110000],
        'KNO3 1 kg' => ['unit' => 'kg', 'buy' => 45000, 'sell' => 50000],
        'Kalsium (Calcium)' => ['unit' => 'bottle', 'buy' => 35000, 'sell' => 40000],
        'Kilapfur' => ['unit' => 'bottle', 'buy' => 175000, 'sell' => 180000],
        'Kran' => ['unit' => 'pcs', 'buy' => 20000, 'sell' => 25000],
        'Lain-lain (Pa Misnen)' => ['unit' => 'pcs', 'buy' => 89500, 'sell' => 94500],
        'Marshal 100 ml' => ['unit' => 'bottle', 'buy' => 12000, 'sell' => 15000],
        'Melon (benih)' => ['unit' => 'bottle', 'buy' => 12000, 'sell' => 15000],
        'Metpipro' => ['unit' => 'bottle', 'buy' => 105000, 'sell' => 110000],
        'NPK Eceran (per kg)' => ['unit' => 'kg', 'buy' => 17000, 'sell' => 22000],
        'Netrea 1 kg' => ['unit' => 'pack', 'buy' => 12800, 'sell' => 16000],
        'Netrea 5 kg' => ['unit' => 'pack', 'buy' => 70000, 'sell' => 75000],
        'Netrea 500 gr' => ['unit' => 'pack', 'buy' => 6400, 'sell' => 8000],
        'Oritone' => ['unit' => 'bottle', 'buy' => 55000, 'sell' => 60000],
        'Phonska / Poska (per kg)' => ['unit' => 'kg', 'buy' => 3680, 'sell' => 3780],
        'Reanga' => ['unit' => 'bottle', 'buy' => 35000, 'sell' => 40000],
        'Semuay' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Sigantung' => ['unit' => 'bottle', 'buy' => 30000, 'sell' => 35000],
        'Spontan' => ['unit' => 'bottle', 'buy' => 50000, 'sell' => 55000],
        'Ultanon' => ['unit' => 'bottle', 'buy' => 16000, 'sell' => 21000],
        'Urea Subsidi (per kg)' => ['unit' => 'kg', 'buy' => 3600, 'sell' => 3700],
    ];

        $days = [
        '2026-09-06' => [
            [null, [['KCL Super Cair 1 Lt', 1, 60000]]],
            [null, [['Abacel 250 ml', 1, 65000]]],
            [null, [['Urea Subsidi (per kg)', 175, 3700], ['Phonska / Poska (per kg)', 175, 3780]]],
            [null, [['Amistartop 100 ml (10)', 1, 115000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Spuyer Lb.4 Lurus Plastik', 1, 15000]]],
            [null, [['Confidor 100 ml Cair', 1, 70000]]],
            [null, [['Kalsium (Calcium)', 1, 40000]]],
            [null, [['Gandasil D 100 gr / Hijau', 3, 13000]]],
            [null, [['Joker 400 gr', 1, 95000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
        ],
        '2026-09-07' => [
            ['Pak Misnen', [['Urea Subsidi (per kg)', 275, 3700], ['Phonska / Poska (per kg)', 250, 3780]]],
            ['Pak Sapmdin', [['Urea Subsidi (per kg)', 50, 3700]]],
            [null, [['Gandasil D 500 gr / Hijau', 1, 55000]]],
            [null, [['MKP Paktani (58)', 1, 60000]]],
            [null, [['KNO3 1 kg', 1, 50000]]],
            [null, [['Hoky 500 ml Cair', 3, 30000]]],
            [null, [['Gatiqa', 3, 20000]]],
            [null, [['Gandasil B 500 gr / Merah', 1, 55000]]],
            [null, [['Gibro Pell', 1, 27000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Melon (benih)', 1, 15000]]],
            [null, [['Amistartop 250 ml (24)', 1, 260000]]],
        ],
        '2026-09-08' => [
            [null, [['Netrea 1 kg', 1, 16000]]],
            [null, [['Fonce', 1, 40000]]],
            [null, [['MKP Paktani (58)', 1, 60000]]],
            [null, [['Trisula 1 Lt', 1, 50000]]],
            [null, [['Decis 250 ml', 1, 80000]]],
            [null, [['Bentan 100 gr 80 WP', 5, 70000]]],
            [null, [['Ti-Gold', 10, 18000]]],
            ['Rw Ade Bewok', [['Urea Subsidi (per kg)', 50, 3700], ['Phonska / Poska (per kg)', 50, 3780]]],
        ],
        '2026-09-09' => [
            [null, [['Urea Subsidi (per kg)', 50, 3700], ['Phonska / Poska (per kg)', 200, 3780]]],
            [null, [['Decis 100 ml', 1, 35000]]],
            [null, [['MKP Paktani (58)', 3, 60000]]],
            [null, [['Tandem 250 ml', 1, 160000]]],
            [null, [['Centazole 250 ml', 1, 120000]]],
            ['Kartama', [['Urea Subsidi (per kg)', 300, 3700], ['Phonska / Poska (per kg)', 300, 3780]]],
            [null, [['Bafis 1 Lt', 1, 75000]]],
            [null, [['Netrea 5 kg', 1, 75000]]],
            [null, [['Gibro Pell', 1, 27000]]],
            [null, [['Spuyer Lb.4 Lurus Plastik', 1, 15000]]],
            [null, [['Gramoxone 250 ml', 1, 25000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Metsulindo 100 gr', 2, 16000]]],
            [null, [['Gib-Grow Saset Biasa', 4, 4500]]],
        ],
        '2026-09-10' => [
            [null, [['Ricestar 100 ml', 2, 65000]]],
            [null, [['Bafis 1 Lt', 1, 75000]]],
            [null, [['Gandasil D 500 gr / Hijau', 2, 55000]]],
            [null, [['Trisula 1 Lt', 2, 45000]]],
            [null, [['Fostin 400 ml', 1, 75000]]],
            [null, [['MKP Paktani (58)', 1, 60000]]],
            [null, [['Klenseet 400 ml', 1, 80000]]],
            [null, [['Kangkung HD', 1, 35000]]],
            [null, [['Round Up 200 ml', 1, 30000]]],
        ],
        '2026-09-11' => [
            [null, [['Gib-Grow Saset Biasa', 4, 4500]]],
            [null, [['Gandasil D 100 gr / Hijau', 1, 13000]]],
            [null, [['Metsulindo 100 gr', 5, 16000]]],
            [null, [['Ricestar 100 ml', 1, 65000]]],
            [null, [['Galon (pestisida)', 1, 100000]]],
            [null, [['Urea Subsidi (per kg)', 250, 3700], ['Phonska / Poska (per kg)', 250, 3780]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Netrea 500 gr', 1, 8000]]],
        ],
        '2026-09-12' => [
            [null, [['Sidabas 500 ml', 1, 40000]]],
            [null, [['Semuay', 2, 35000]]],
            [null, [['Amistartop 250 ml (24)', 2, 260000]]],
            [null, [['Ricestar 100 ml', 1, 65000]]],
            [null, [['Rumpas 100 ml', 1, 55000]]],
            [null, [['Starban 400 ml', 1, 75000]]],
            [null, [['Imidatrin 200 ml', 1, 40000]]],
            [null, [['Fastac 100 ml', 1, 40000]]],
        ],
        '2026-09-14' => [
            [null, [['Score 250 ml', 1, 170000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Gandasil D 100 gr / Hijau', 1, 13000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['MKP Paktani (58)', 1, 60000]]],
            [null, [['KNO3 1 kg', 1, 50000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Kangkung HD', 1, 35000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Gandasil D 500 gr / Hijau', 1, 55000]]],
            [null, [['Spontan', 1, 55000]]],
            [null, [['Gib-Grow Panen', 8, 8000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Netrea 5 kg', 1, 75000]]],
        ],
        '2026-09-15' => [
            [null, [['Joker 1 kg', 1, 210000]]],
            [null, [['Sigantung', 1, 35000]]],
            [null, [['KCL HS 500 ml', 1, 17000]]],
            [null, [['Netrea 1 kg', 1, 16000]]],
            [null, [['Gandasil D 100 gr / Hijau', 1, 13000]]],
            ['Bandi Eguyan', [['Urea Subsidi (per kg)', 525, 3700], ['Phonska / Poska (per kg)', 300, 3780]]],
            [null, [['Starban 400 ml', 1, 75000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Amistartop 100 ml (10)', 1, 115000]]],
            [null, [['Score 250 ml', 1, 170000]]],
            [null, [['Booster', 1, 60000]]],
        ],
        '2026-09-16' => [
            [null, [['Urea Subsidi (per kg)', 500, 3700], ['Phonska / Poska (per kg)', 500, 3780]]],
            [null, [['Bayam 50 gr (Eceran)', 1, 15000]]],
            [null, [['Gibro Pell', 1, 27000]]],
        ],
        '2026-09-17' => [
            ['Niki', [['Urea Subsidi (per kg)', 400, 3700]]],
            [null, [['Ultradap', 6, 55000]]],
            [null, [['Antracol 1 kg', 3, 170000]]],
            ['Pak Misnen', [['Lain-lain (Pa Misnen)', 1, 94500]]],
            [null, [['Kangkung HD', 1, 35000]]],
        ],
        '2026-09-18' => [
            [null, [['Rumpas 100 ml', 1, 55000]]],
            [null, [['De-Star 250 ml', 2, 70000]]],
            [null, [['Patrol 10 ml (BE)', 1, 20000]]],
            [null, [['NPK Eceran (per kg)', 2, 22000]]],
            [null, [['Sidabas 500 ml', 1, 40000]]],
            [null, [['Gibro Pell', 2, 27000]]],
            [null, [['Gatiqa', 2, 20000]]],
            [null, [['Decis 500 ml', 2, 150000]]],
            [null, [['Aplaud 400 gr', 2, 80000]]],
            [null, [['NPK Eceran (per kg)', 10, 21000]]],
            [null, [['Gandasil D 100 gr / Hijau', 1, 13000]]],
            [null, [['KCL Super Cair 1 Lt', 6, 50000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Regent 100 ml', 1, 50000]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Netrea 1 kg', 1, 16000]]],
            [null, [['Oritone', 1, 60000]]],
        ],
        '2026-09-19' => [
            [null, [['Antracol 1 kg', 1, 90000]]],
            [null, [['Gramoxone 250 ml', 1, 25000]]],
            [null, [['Bestox 250 ml', 1, 50000]]],
            [null, [['Netrea 1 kg', 2, 15000]]],
            [null, [['KCL Super Cair 1 Lt', 2, 60000]]],
            [null, [['Bafis 1 Lt', 1, 75000]]],
            [null, [['De-Star 250 ml', 2, 70000]]],
            [null, [['Ares 100 ml', 1, 25000]]],
        ],
        '2026-09-21' => [
            [null, [['Bafis 1 Lt', 2, 75000]]],
            [null, [['Imidatrin 500 ml', 1, 65000]]],
            [null, [['Rumpas 100 ml', 1, 55000]]],
            [null, [['Round Up 200 ml', 1, 30000]]],
            [null, [['Oritone', 1, 60000]]],
            [null, [['Gempur 1 Liter', 1, 80000]]],
            ['Kartama', [['Urea Subsidi (per kg)', 50, 3700]]],
        ],
        '2026-09-22' => [
            [null, [['Stik Teleskopik Swan', 1, 35000]]],
            [null, [['Kran', 1, 25000]]],
            [null, [['Spuyer Lb.5', 1, 25000]]],
            [null, [['Emy', 1, 35000]]],
            [null, [['Booster', 2, 65000]]],
            [null, [['Comet 900 gram', 1, 95000]]],
            [null, [['Ultanon', 1, 21000]]],
            [null, [['Metsulindo 100 gr', 1, 16000]]],
            [null, [['Urea Subsidi (per kg)', 600, 3700], ['Phonska / Poska (per kg)', 600, 3780]]],
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['MKP Paktani (58)', 1, 60000]]],
            [null, [['Decis 250 ml', 3, 100000]]],
        ],
        '2026-09-23' => [
            [null, [['Furadan / Bks', 3, 40000]]],
            [null, [['Inpari (benih padi)', 4, 110000]]],
            [null, [['Inpari (benih padi)', 2, 110000]]],
            [null, [['Phonska / Poska (per kg)', 75, 3780]]],
            [null, [['Inpari (benih padi)', 3, 110000]]],
            [null, [['Netrea 5 kg', 2, 55000]]],
            [null, [['Marshal 100 ml', 2, 15000]]],
            [null, [['Kilapfur', 1, 180000]]],
            [null, [['Aplaud 400 gr', 1, 65000]]],
            [null, [['Reanga', 1, 40000]]],
            [null, [['Metsulindo 100 gr', 1, 16000]]],
        ],
        '2026-09-24' => [
            [null, [['Phonska / Poska (per kg)', 150, 3780], ['Urea Subsidi (per kg)', 250, 3700]]],
            [null, [['Metsulindo 100 gr', 2, 16000]]],
            [null, [['Oritone', 1, 60000]]],
            [null, [['Metpipro', 1, 110000]]],
        ],
        '2026-09-25' => [
            [null, [['NPK Eceran (per kg)', 1, 22000]]],
            [null, [['Gandasil D 100 gr / Hijau', 1, 13000]]],
            [null, [['Bafis 1 Lt', 1, 75000]]],
            [null, [['Topsin 500 ml', 1, 85000]]],
            [null, [['KCL Super Cair 1 Lt', 1, 60000]]],
            [null, [['Gandasil D 100 gr / Hijau', 2, 13000]]],
        ],
        '2026-09-26' => [
            [null, [['Bafis 1 Lt', 1, 75000]]],
            [null, [['Bintang', 1, 30000]]],
            [null, [['Urea Subsidi (per kg)', 225, 3700], ['Phonska / Poska (per kg)', 225, 3780]]],
            [null, [['Centazole 80 ml', 1, 50000]]],
            [null, [['Bafis 1 Lt', 1, 75000]]],
        ],
    ];

        DB::transaction(function () use ($catalog, $extras, $days) {
            $now = now();
            $userId = $this->cashierId();
            $categoryId = $this->categoryId();

            // total qty per produk -> untuk stok batch
            $needed = [];
            foreach ($days as $rows) {
                foreach ($rows as [, $items]) {
                    foreach ($items as [$name, $qty]) {
                        $needed[$name] = ($needed[$name] ?? 0) + $qty;
                    }
                }
            }

            // produk + batch
            $productIds = [];
            $batchIds = [];
            foreach ($needed as $name => $qtyNeeded) {
                $info = $catalog[$name] ?? $extras[$name];
                $product = DB::table('products')->where('name', $name)->first();
                if ($product) {
                    $productId = $product->id;
                } else {
                    $productId = DB::table('products')->insertGetId([
                        'category_id' => $categoryId,
                        'sku' => 'SD-' . strtoupper(substr(md5($name), 0, 8)),
                        'barcode' => null,
                        'name' => $name,
                        'image' => null,
                        'active_ingredient' => null,
                        'unit' => $info['unit'],
                        'min_stock_level' => 30,
                        'purchase_price' => $info['buy'],
                        'selling_price' => $info['sell'],
                        'is_restricted' => false,
                        'created_at' => '2026-09-01 08:00:00',
                        'updated_at' => '2026-09-01 08:00:00',
                    ]);
                }
                $productIds[$name] = $productId;

                $isKg = $info['unit'] === 'kg';
                $batchIds[$name] = DB::table('product_batches')->insertGetId([
                    'product_id' => $productId,
                    'batch_number' => 'SEED-SEP26-' . $productId,
                    'purchase_price' => $info['buy'],
                    'selling_price' => $info['sell'],
                    'stock_quantity' => $qtyNeeded + ($isKg ? 2000 : 40),
                    'manufactured_date' => '2026-06-01',
                    'expiration_date' => '2028-12-31',
                    'created_at' => '2026-09-01 08:00:00',
                    'updated_at' => '2026-09-01 08:00:00',
                ]);
            }

            // pelanggan bernama di buku
            $customerIds = [];

            foreach ($days as $date => $rows) {
                $seq = 0;
                $minute = 8 * 60 + 15; // mulai 08:15
                foreach ($rows as [$customerName, $items]) {
                    $seq++;
                    $minute += random_int(7, 38);
                    $time = sprintf('%s %02d:%02d:%02d', $date, intdiv($minute, 60) % 24, $minute % 60, random_int(0, 59));

                    $customerId = null;
                    if ($customerName) {
                        $customerIds[$customerName] ??= DB::table('customers')->where('name', $customerName)->value('id')
                            ?? DB::table('customers')->insertGetId([
                                'name' => $customerName,
                                'phone' => null,
                                'farmer_group' => null,
                                'address' => null,
                                'credit_limit' => 0,
                                'current_debt' => 0,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ]);
                        $customerId = $customerIds[$customerName];
                    }

                    $subtotal = 0;
                    foreach ($items as [, $qty, $price]) {
                        $subtotal += $qty * $price;
                    }

                    $saleId = DB::table('sales')->insertGetId([
                        'invoice_number' => sprintf('INV-%s-S%03d', str_replace('-', '', $date), $seq),
                        'customer_id' => $customerId,
                        'user_id' => $userId,
                        'subtotal' => $subtotal,
                        'discount' => 0,
                        'tax' => 0,
                        'grand_total' => $subtotal,
                        'paid_amount' => $subtotal,
                        'change_amount' => 0,
                        'payment_method' => 'cash',
                        'payment_status' => 'paid',
                        'due_date' => null,
                        'notes' => null,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]);

                    foreach ($items as [$name, $qty, $price]) {
                        DB::table('sale_items')->insert([
                            'sale_id' => $saleId,
                            'product_id' => $productIds[$name],
                            'batch_id' => $batchIds[$name],
                            'unit_price' => $price,
                            'quantity' => $qty,
                            'discount' => 0,
                            'subtotal' => $qty * $price,
                            'created_at' => $time,
                            'updated_at' => $time,
                        ]);
                        DB::table('product_batches')->where('id', $batchIds[$name])->decrement('stock_quantity', $qty);
                    }
                }
            }
        });
    }

    private function cashierId(): int
    {
        $id = DB::table('users')->orderBy('id')->value('id');
        if ($id) {
            return (int) $id;
        }
        $roleId = DB::table('roles')->where('name', 'kasir')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'kasir', 'created_at' => now(), 'updated_at' => now()]);

        return (int) DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'name' => 'Kasir',
            'username' => 'kasir',
            'password' => Hash::make('password'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function categoryId(): int
    {
        return (int) (DB::table('categories')->where('slug', 'other')->value('id')
            ?? DB::table('categories')->insertGetId([
                'name' => 'Other',
                'slug' => 'other',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }
}
