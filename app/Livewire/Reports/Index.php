<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reports')]
class Index extends Component
{
    #[Computed]
    public function salesByCategory()
    {
        $rows = Category::query()
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name, COALESCE(SUM(sale_items.subtotal), 0) as revenue, COALESCE(SUM(sale_items.quantity), 0) as units')
            ->get();

        $total = (float) $rows->sum('revenue');

        return $rows
            ->map(fn ($row) => [
                'name' => $row->name,
                'revenue' => (float) $row->revenue,
                'units' => (int) $row->units,
                'share' => $total > 0 ? ((float) $row->revenue / $total) * 100 : 0,
            ])
            ->sortByDesc('revenue')
            ->values();
    }

    #[Computed]
    public function topDebtors()
    {
        return Customer::query()
            ->where('current_debt', '>', 0)
            ->orderByDesc('current_debt')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function monthlyRevenue(): float
    {
        return (float) Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');
    }

    #[Computed]
    public function outstandingReceivables(): float
    {
        return (float) Customer::sum('current_debt');
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
