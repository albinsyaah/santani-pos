<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Farmer receivables report')]
class Receivables extends Component
{
    #[Url(as: 'search', history: true)]
    public string $search = '';

    protected function bucketFor(?\Carbon\CarbonInterface $dueDate, \Carbon\CarbonInterface $today): string
    {
        if (! $dueDate || $dueDate->greaterThanOrEqualTo($today)) {
            return 'current';
        }

        $daysOverdue = $today->diffInDays($dueDate);

        return match (true) {
            $daysOverdue <= 30 => 'b1_30',
            $daysOverdue <= 60 => 'b31_60',
            $daysOverdue <= 90 => 'b61_90',
            default => 'b90_plus',
        };
    }

    public function render()
    {
        $today = now()->startOfDay();

        $sales = Sale::query()
            ->whereNotNull('customer_id')
            ->where('payment_status', '!=', 'paid')
            ->when($this->search, fn ($q) => $q->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->with('customer')
            ->get();

        $emptyBuckets = ['current' => 0.0, 'b1_30' => 0.0, 'b31_60' => 0.0, 'b61_90' => 0.0, 'b90_plus' => 0.0];

        $rows = $sales
            ->groupBy('customer_id')
            ->map(function ($customerSales) use ($today, $emptyBuckets) {
                $customer = $customerSales->first()->customer;
                $buckets = $emptyBuckets;

                foreach ($customerSales as $sale) {
                    $unpaid = $sale->unpaid_balance;

                    if ($unpaid <= 0) {
                        continue;
                    }

                    $buckets[$this->bucketFor($sale->due_date, $today)] += $unpaid;
                }

                return [
                    'customer' => $customer,
                    'buckets' => $buckets,
                    'total' => array_sum($buckets),
                ];
            })
            ->filter(fn ($row) => $row['customer'] && $row['total'] > 0)
            ->sortByDesc('total')
            ->values();

        $totals = $emptyBuckets;
        foreach ($rows as $row) {
            foreach ($row['buckets'] as $key => $value) {
                $totals[$key] += $value;
            }
        }
        $grandTotal = array_sum($totals);

        return view('livewire.reports.receivables', [
            'rows' => $rows,
            'totals' => $totals,
            'grandTotal' => $grandTotal,
        ]);
    }
}
