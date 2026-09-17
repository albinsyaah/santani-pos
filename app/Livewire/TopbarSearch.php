<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TopbarSearch extends Component
{
    public string $query = '';

    #[Computed]
    public function products(): Collection
    {
        if (mb_strlen($this->query) < 2) {
            return collect();
        }

        return Product::query()
            ->where('name', 'like', "%{$this->query}%")
            ->orWhere('sku', 'like', "%{$this->query}%")
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function customers(): Collection
    {
        if (mb_strlen($this->query) < 2) {
            return collect();
        }

        return Customer::query()
            ->where('name', 'like', "%{$this->query}%")
            ->orWhere('phone', 'like', "%{$this->query}%")
            ->limit(5)
            ->get();
    }

    public function clear(): void
    {
        $this->query = '';
    }

    public function render()
    {
        return view('livewire.topbar-search');
    }
}
