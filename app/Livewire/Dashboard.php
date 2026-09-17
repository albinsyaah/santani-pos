<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /** Whether the dashboard is currently polling for fresh data. */
    public bool $autoUpdating = false;

    public function toggleAutoUpdate(): void
    {
        $this->autoUpdating = ! $this->autoUpdating;
    }

    /** No-op action target for wire:poll — the render cycle itself refreshes every computed property. */
    public function tick(): void
    {
        unset($this->metrics, $this->weeklyOrders, $this->topProducts, $this->recentTransactions, $this->paymentMethods, $this->lowStock);
    }

    #[Computed]
    public function metrics(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $income = (float) Sale::whereBetween('created_at', [$weekStart, $weekEnd])->sum('grand_total');
        $incomeLast = (float) Sale::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('grand_total');

        $expense = (float) \App\Models\Purchase::whereBetween('created_at', [$weekStart, $weekEnd])->sum('total_amount');
        $expenseLast = (float) \App\Models\Purchase::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('total_amount');

        $orders = Sale::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $ordersLast = Sale::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $profit = $income - $expense;
        $profitLast = $incomeLast - $expenseLast;

        return [
            [
                'label' => __('Total income'),
                'value' => 'Rp '.number_format($income, 0, ',', '.'),
                'icon' => 'income',
                ...$this->delta($income, $incomeLast),
            ],
            [
                'label' => __('Total expense'),
                'value' => 'Rp '.number_format($expense, 0, ',', '.'),
                'icon' => 'expense',
                ...$this->delta($expense, $expenseLast, invert: true),
            ],
            [
                'label' => __('Net profit'),
                'value' => 'Rp '.number_format($profit, 0, ',', '.'),
                'icon' => 'profit',
                ...$this->delta($profit, $profitLast),
            ],
            [
                'label' => __('Total orders'),
                'value' => number_format($orders),
                'icon' => 'orders',
                ...$this->delta($orders, $ordersLast),
            ],
        ];
    }

    private function delta(float $current, float $previous, bool $invert = false): array
    {
        if ($previous == 0.0) {
            $direction = $current > 0 ? 'up' : 'down';
            $percent = $current > 0 ? 100 : 0;
        } else {
            $percent = (($current - $previous) / abs($previous)) * 100;
            $direction = $percent >= 0 ? 'up' : 'down';
        }

        // For expenses, a drop is the "good" direction visually stays down/red-free — we simply
        // report the raw movement; the icon colour follows direction regardless of metric type.
        if ($invert) {
            // no-op placeholder to keep signature symmetric/expressive for future styling rules
        }

        return [
            'direction' => $direction,
            'delta' => ($percent >= 0 ? '+' : '').number_format($percent, 1).'%',
        ];
    }

    #[Computed]
    public function weeklyOrders(): array
    {
        $start = now()->startOfWeek();
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return collect($labels)->map(function (string $label, int $i) use ($start) {
            $day = $start->clone()->addDays($i);

            return [
                'day' => $label,
                'value' => Sale::whereDate('created_at', $day)->count(),
            ];
        })->all();
    }

    /** Builds the SVG line/area path + axis labels for the weekly orders chart, server-side. */
    #[Computed]
    public function chart(): array
    {
        $data = $this->weeklyOrders;
        $left = 34;
        $right = 640;
        $top = 15;
        $bottom = 175;

        $maxRaw = max(array_column($data, 'value'));
        $maxVal = max(5, (int) (ceil(($maxRaw * 1.25) / 5) * 5));

        $count = count($data);
        $step = $count > 1 ? ($right - $left) / ($count - 1) : 0;

        $points = [];
        foreach ($data as $i => $d) {
            $points[] = [
                'x' => $left + $step * $i,
                'y' => $bottom - ($maxVal > 0 ? ($d['value'] / $maxVal) : 0) * ($bottom - $top),
                'day' => $d['day'],
                'value' => $d['value'],
            ];
        }

        $line = sprintf('M%s %s', $points[0]['x'], $points[0]['y']);
        for ($i = 0; $i < count($points) - 1; $i++) {
            $p0 = $points[$i];
            $p1 = $points[$i + 1];
            $midX = ($p0['x'] + $p1['x']) / 2;
            $line .= sprintf(' C%s %s, %s %s, %s %s', $midX, $p0['y'], $midX, $p1['y'], $p1['x'], $p1['y']);
        }
        $area = $line." L{$points[count($points) - 1]['x']} {$bottom} L{$points[0]['x']} {$bottom} Z";

        $yLabels = [];
        for ($i = 0; $i <= 4; $i++) {
            $yLabels[] = (int) round($maxVal - ($maxVal / 4) * $i);
        }

        $peak = collect($points)->sortByDesc('value')->first();

        return compact('points', 'line', 'area', 'yLabels', 'top', 'bottom', 'left', 'right', 'peak');
    }

    #[Computed]
    public function topProducts()
    {
        $rows = SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('sale', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name')
            ->limit(5)
            ->get();

        $max = max(1, (int) ($rows->max('total_qty') ?? 1));

        return $rows->map(fn ($r) => [
            'label' => $r->product?->name ?? __('Unknown product'),
            'count' => (int) $r->total_qty,
            'max' => $max,
        ]);
    }

    #[Computed]
    public function recentTransactions()
    {
        return Sale::query()
            ->latest()
            ->with('customer:id,name')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function paymentMethods()
    {
        $colors = [
            'cash' => '#ffcf4d',
            'qris' => '#c6f135',
            'transfer' => '#61b0ff',
            'credit' => '#ff9e40',
        ];

        return Sale::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'label' => \App\Support\Labels::paymentMethod($r->payment_method),
                'count' => (int) $r->total,
                'color' => $colors[$r->payment_method] ?? '#d9dde0',
            ]);
    }

    #[Computed]
    public function lowStock()
    {
        return Product::query()
            ->get()
            ->filter(fn (Product $p) => $p->total_stock <= $p->min_stock_level)
            ->sortBy('total_stock')
            ->take(5)
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'qty' => __(':qty :unit left', ['qty' => $p->total_stock, 'unit' => $p->unit]),
            ]);
    }

    #[Computed]
    public function activeCashiers(): int
    {
        return User::where('status', 'active')->count();
    }

    #[Computed]
    public function totalProducts(): int
    {
        return Product::count();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
