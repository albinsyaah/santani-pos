<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Suppliers')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $contactPerson = '';

    public string $phone = '';

    public string $address = '';

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
        $this->reset(['name', 'contactPerson', 'phone', 'address']);
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEditModal(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);

        $this->editingId = $supplier->id;
        $this->name = $supplier->name;
        $this->contactPerson = $supplier->contact_person ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';

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
            'contactPerson' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'contact_person' => $validated['contactPerson'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'address' => $validated['address'] ?: null,
        ];

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($payload);
            $this->flash = __(':name was updated.', ['name' => $validated['name']]);
        } else {
            Supplier::create($payload);
            $this->flash = __(':name was added.', ['name' => $validated['name']]);
        }

        $this->showFormModal = false;
        $this->editingId = null;
        $this->resetPage();
    }

    public function askDelete(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->deletingId = $supplier->id;
        $this->deletingLabel = $supplier->name;
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

        $supplier = Supplier::find($this->deletingId);

        if ($supplier && $supplier->purchases()->exists()) {
            $this->flash = __(':name has purchase history and cannot be deleted.', ['name' => $supplier->name]);
            $this->cancelDelete();

            return;
        }

        if ($supplier) {
            $name = $supplier->name;
            $supplier->delete();
            $this->flash = __(':name was removed.', ['name' => $name]);
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.suppliers.index', ['suppliers' => $suppliers]);
    }
}
