<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Farmers & customers')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $farmerGroup = '';

    public string $address = '';

    public string $creditLimit = '0';

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public string $flash = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->reset(['name', 'phone', 'farmerGroup', 'address', 'creditLimit']);
        $this->creditLimit = '0';
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEditModal(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->farmerGroup = $customer->farmer_group ?? '';
        $this->address = $customer->address ?? '';
        $this->creditLimit = (string) $customer->credit_limit;

        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'farmerGroup' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'creditLimit' => ['required', 'numeric', 'min:0'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: null,
            'farmer_group' => $validated['farmerGroup'] ?: null,
            'address' => $validated['address'] ?: null,
            'credit_limit' => $validated['creditLimit'],
        ];

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($payload);
            $this->flash = __(':name was updated.', ['name' => $validated['name']]);
        } else {
            Customer::create($payload + ['current_debt' => 0]);
            $this->flash = __(':name was added.', ['name' => $validated['name']]);
        }

        $this->showFormModal = false;
        $this->editingId = null;
        $this->resetPage();
    }

    public function askDelete(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->deletingId = $customer->id;
        $this->deletingLabel = $customer->name;
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

        $customer = Customer::find($this->deletingId);

        if ($customer && (float) $customer->current_debt > 0) {
            $this->flash = __(':name still has an outstanding balance and cannot be deleted.', ['name' => $customer->name]);
            $this->cancelDelete();

            return;
        }

        if ($customer) {
            $name = $customer->name;
            $customer->delete();
            $this->flash = __(':name was removed.', ['name' => $name]);
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('farmer_group', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.customers.index', ['customers' => $customers]);
    }
}
