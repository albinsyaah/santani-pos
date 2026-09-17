<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class ReceiptController extends Controller
{
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);

        return view('receipts.show', ['sale' => $sale]);
    }
}
