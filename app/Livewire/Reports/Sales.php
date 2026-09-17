<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Sales report')]
class Sales extends Component
{
    use WithPagination;

    #[Url(as: 'period', history: true)]
    public string $period = 'month';

    public function updatingPeriod(): void
    {
        $this->resetPage();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateRange(): array
    {
        return match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'all' => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    protected function costOfItems($items): float
    {
        return (float) $items->sum(function (SaleItem $item) {
            $unitCost = $item->batch?->purchase_price ?? $item->product?->purchase_price ?? 0;

            return (float) $unitCost * $item->quantity;
        });
    }

    public function saleCost(Sale $sale): float
    {
        return $this->costOfItems($sale->items);
    }

    public function saleProfit(Sale $sale): float
    {
        return (float) $sale->grand_total - $this->saleCost($sale);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $baseQuery = Sale::query()->whereBetween('created_at', [$from, $to]);

        $totalRevenue = (float) (clone $baseQuery)->sum('grand_total');
        $transactionCount = (clone $baseQuery)->count();
        $averageSale = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        $totalCost = $this->costOfItems(
            SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
                ->with(['batch:id,purchase_price', 'product:id,purchase_price'])
                ->get()
        );

        $grossProfit = $totalRevenue - $totalCost;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        $byMethod = (clone $baseQuery)
            ->selectRaw('payment_method, COUNT(*) as tx_count, SUM(grand_total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'method' => $r->payment_method,
                'label' => \App\Support\Labels::paymentMethod($r->payment_method),
                'count' => (int) $r->tx_count,
                'total' => (float) $r->total,
                'share' => $totalRevenue > 0 ? ((float) $r->total / $totalRevenue) * 100 : 0,
            ]);

        $sales = (clone $baseQuery)
            ->with(['customer', 'items.batch:id,purchase_price', 'items.product:id,purchase_price'])
            ->latest()
            ->paginate(10);

        return view('livewire.reports.sales', [
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
            'averageSale' => $averageSale,
            'grossProfit' => $grossProfit,
            'grossMargin' => $grossMargin,
            'byMethod' => $byMethod,
            'sales' => $sales,
        ]);
    }
}
