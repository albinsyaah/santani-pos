<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Product Data')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    public ?int $categoryId = null;

    public bool $lowStockOnly = false;

    public bool $showFormModal = false;

    /** Product being edited, or null when the form is in "create" mode. */
    public ?int $editingId = null;

    // Product form state
    public string $name = '';

    public string $sku = '';

    public string $unit = 'kg';

    public ?int $formCategoryId = null;

    public string $activeIngredient = '';

    public string $purchasePrice = '';

    public string $sellingPrice = '';

    public string $minStockLevel = '10';

    public bool $isRestricted = false;

    // Delete confirmation state
    public ?int $deletingId = null;

    public string $deletingLabel = '';

    public string $flash = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingLowStockOnly(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->reset(['name', 'sku', 'unit', 'formCategoryId', 'activeIngredient', 'purchasePrice', 'sellingPrice', 'minStockLevel', 'isRestricted']);
        $this->sku = Product::generateSku();
        $this->unit = 'kg';
        $this->minStockLevel = '10';
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEditModal(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->unit = $product->unit;
        $this->formCategoryId = $product->category_id;
        $this->activeIngredient = $product->active_ingredient ?? '';
        $this->purchasePrice = (string) $product->purchase_price;
        $this->sellingPrice = (string) $product->selling_price;
        $this->minStockLevel = (string) $product->min_stock_level;
        $this->isRestricted = (bool) $product->is_restricted;

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
        // A cleared SKU is filled in automatically instead of failing validation.
        if (trim($this->sku) === '') {
            $this->sku = Product::generateSku();
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->editingId)],
            'unit' => ['required', 'string', 'max:20'],
            'formCategoryId' => ['required', 'exists:categories,id'],
            'activeIngredient' => ['nullable', 'string', 'max:255'],
            'purchasePrice' => ['required', 'numeric', 'min:0'],
            'sellingPrice' => ['required', 'numeric', 'min:0'],
            'minStockLevel' => ['required', 'integer', 'min:0'],
        ]);

        $payload = [
            'category_id' => $validated['formCategoryId'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'active_ingredient' => $validated['activeIngredient'] ?: null,
            'unit' => $validated['unit'],
            'min_stock_level' => $validated['minStockLevel'],
            'purchase_price' => $validated['purchasePrice'],
            'selling_price' => $validated['sellingPrice'],
            'is_restricted' => $this->isRestricted,
        ];

        if ($this->editingId) {
            Product::findOrFail($this->editingId)->update($payload);
            $this->flash = __(':name was updated.', ['name' => $validated['name']]);
        } else {
            Product::create($payload);
            $this->flash = __(':name was added to the catalog.', ['name' => $validated['name']]);
        }

        $this->showFormModal = false;
        $this->editingId = null;
        $this->resetPage();
    }

    public function askDelete(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->deletingId = $product->id;
        $this->deletingLabel = $product->name;
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

        $product = Product::find($this->deletingId);

        if ($product) {
            $name = $product->name;
            $product->delete();
            $this->flash = __(':name was removed.', ['name' => $name]);
        }

        $this->cancelDelete();
    }

    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function render()
    {
        $products = Product::query()
            ->with('category')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('active_ingredient', 'like', "%{$this->search}%");
                });
            })
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->orderBy('name')
            ->paginate(10);

        if ($this->lowStockOnly) {
            $products->setCollection(
                $products->getCollection()->filter(fn (Product $p) => $p->total_stock <= $p->min_stock_level)
            );
        }

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => $this->categories(),
        ]);
    }
}
