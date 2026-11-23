<?php

use App\Http\Controllers\ReceiptController;
use App\Livewire\Batches\Index as BatchesIndex;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Http\Middleware\SetLocale;
use App\Livewire\CustomerPayments\Index as CustomerPaymentsIndex;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Pos\Pending as PosPending;
use App\Livewire\Pos\Terminal as PosTerminal;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Purchases\Create as PurchasesCreate;
use App\Livewire\Purchases\Index as PurchasesIndex;
use App\Livewire\Reports\Expirations as ReportsExpirations;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Reports\Receivables as ReportsReceivables;
use App\Livewire\Reports\Sales as ReportsSales;
use App\Livewire\Sales\Index as SalesIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Stock\Opening as StockOpening;
use App\Livewire\Suppliers\Index as SuppliersIndex;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

Route::livewire('/', Dashboard::class)->name('dashboard');
Route::livewire('/pos', PosTerminal::class)->name('pos.terminal');
Route::livewire('/pos/pending', PosPending::class)->name('pos.pending');
Route::livewire('/sales', SalesIndex::class)->name('sales.index');
Route::get('/sales/{sale}/receipt', [ReceiptController::class, 'show'])->name('sales.receipt');
Route::livewire('/products', ProductsIndex::class)->name('products.index');
Route::livewire('/products/stock', StockOpening::class)->name('stock.opening');
Route::livewire('/batches', BatchesIndex::class)->name('batches.index');
Route::livewire('/categories', CategoriesIndex::class)->name('categories.index');
Route::livewire('/purchases', PurchasesIndex::class)->name('purchases.index');
Route::livewire('/purchases/create', PurchasesCreate::class)->name('purchases.create');
Route::livewire('/customers', CustomersIndex::class)->name('customers.index');
Route::livewire('/customer-payments', CustomerPaymentsIndex::class)->name('customer-payments.index');
Route::livewire('/suppliers', SuppliersIndex::class)->name('suppliers.index');
Route::livewire('/reports', ReportsIndex::class)->name('reports.index');
Route::livewire('/reports/sales', ReportsSales::class)->name('reports.sales');
Route::livewire('/reports/receivables', ReportsReceivables::class)->name('reports.receivables');
Route::livewire('/reports/expirations', ReportsExpirations::class)->name('reports.expirations');
Route::livewire('/settings', SettingsIndex::class)->name('settings.index');
