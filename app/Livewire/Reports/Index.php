<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
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
        return Category::with('products:id,category_id')
            ->get()
            ->map(function (Category $category) {
                $productIds = $category->products->pluck('id');

                return [
                    'name' => $category->name,
                    'revenue' => (float) SaleItem::whereIn('product_id', $productIds)->sum('subtotal'),
                ];
            })
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
