<?php

namespace App\Livewire\CustomerPayments;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Receivables payments')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    public bool $showFormModal = false;

    public string $customerId = '';

    public string $saleId = '';

    public string $amount = '';

    public string $paymentMethod = 'cash';

    public string $paymentDate = '';

    public string $referenceNumber = '';

    public string $notes = '';

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public string $flash = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerId(): void
    {
        $this->saleId = '';
    }

    public function openCreateModal(): void
    {
        $this->reset(['customerId', 'saleId', 'amount', 'referenceNumber', 'notes']);
        $this->paymentMethod = 'cash';
        $this->paymentDate = now()->format('Y-m-d');
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
    }

    public function customers()
    {
        return Customer::orderBy('name')->get();
    }

    public function openSales()
    {
        if (! $this->customerId) {
            return collect();
        }

        return Sale::where('customer_id', $this->customerId)
            ->where('payment_status', '!=', 'paid')
            ->latest()
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'customerId' => ['required', 'exists:customers,id'],
            'saleId' => ['nullable', 'exists:sales,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paymentMethod' => ['required', 'in:cash,transfer'],
            'paymentDate' => ['required', 'date'],
            'referenceNumber' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated) {
            $customer = Customer::findOrFail($validated['customerId']);
            $amount = (float) $validated['amount'];

            CustomerPayment::create([
                'customer_id' => $customer->id,
                'sale_id' => $validated['saleId'] ?: null,
                'user_id' => User::query()->value('id') ?? 1,
                'amount' => $amount,
                'payment_method' => $validated['paymentMethod'],
                'payment_date' => $validated['paymentDate'],
                'reference_number' => $validated['referenceNumber'] ?: null,
                'notes' => $validated['notes'] ?: null,
            ]);

            $customer->update([
                'current_debt' => max(0, $customer->current_debt - $amount),
            ]);

            if (! empty($validated['saleId'])) {
                $sale = Sale::find($validated['saleId']);

                if ($sale) {
                    $newPaidAmount = min($sale->grand_total, $sale->paid_amount + $amount);

                    $sale->update([
                        'paid_amount' => $newPaidAmount,
                        'payment_status' => match (true) {
                            $newPaidAmount >= (float) $sale->grand_total => 'paid',
                            $newPaidAmount > 0 => 'partial',
                            default => 'unpaid',
                        },
                    ]);
                }
            }

            $this->flash = __('Payment of Rp :amount was recorded for :name.', [
                'amount' => number_format($amount, 0, ',', '.'),
                'name' => $customer->name,
            ]);
        });

        $this->showFormModal = false;
        $this->resetPage();
    }

    public function askDelete(int $paymentId): void
    {
        $payment = CustomerPayment::with('customer')->findOrFail($paymentId);
        $this->deletingId = $payment->id;
        $this->deletingLabel = __('payment of Rp :amount', ['amount' => number_format((float) $payment->amount, 0, ',', '.')]);
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingLabel = '';
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deletingId) {
            return;
        }

        DB::transaction(function () {
            $payment = CustomerPayment::find($this->deletingId);

            if (! $payment) {
                return;
            }

            $customer = $payment->customer;
            if ($customer) {
                $customer->update([
                    'current_debt' => $customer->current_debt + $payment->amount,
                ]);
            }

            if ($payment->sale_id) {
                $sale = Sale::find($payment->sale_id);

                if ($sale) {
                    $newPaidAmount = max(0, $sale->paid_amount - $payment->amount);

                    $sale->update([
                        'paid_amount' => $newPaidAmount,
                        'payment_status' => match (true) {
                            $newPaidAmount >= (float) $sale->grand_total => 'paid',
                            $newPaidAmount > 0 => 'partial',
                            default => 'unpaid',
                        },
                    ]);
                }
            }

            $payment->delete();
            $this->flash = __('Payment was removed.');
        });

        $this->cancelDelete();
    }

    public function render()
    {
        $payments = CustomerPayment::query()
            ->with(['customer', 'sale'])
            ->when($this->search, function ($q) {
                $q->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('reference_number', 'like', "%{$this->search}%");
            })
            ->orderByDesc('payment_date')
            ->paginate(10);

        return view('livewire.customer-payments.index', [
            'payments' => $payments,
            'customers' => $this->customers(),
            'openSales' => $this->openSales(),
        ]);
    }
}
