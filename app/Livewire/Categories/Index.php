<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Categories & Active Ingredients')]
class Index extends Component
{
    use WithPagination;

    /** categories | ingredients */
    #[Url(as: 'tab', history: true)]
    public string $tab = 'categories';

    #[Url(as: 'search', history: true)]
    public string $search = '';

    // --- Category form state ---
    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public ?int $deletingId = null;

    public string $deletingLabel = '';

    // --- Active ingredient rename state ---
    public bool $showIngredientModal = false;

    /** The ingredient value as currently stored on products. */
    public string $originalIngredient = '';

    public string $ingredientName = '';

    public string $flash = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['categories', 'ingredients'], true) ? $tab : 'categories';
        $this->search = '';
        $this->resetPage();
    }

    // ---------------------------------------------------------------
    // Categories
    // ---------------------------------------------------------------

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->reset(['name', 'slug', 'description']);
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';

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
        // Fall back to a slug derived from the name so the field stays optional.
        if (trim($this->slug) === '') {
            $this->slug = Str::slug($this->name);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => __('name'),
            'slug' => __('slug'),
            'description' => __('description'),
        ]);

        $payload = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($payload);
            $this->flash = __(':name was updated.', ['name' => $validated['name']]);
        } else {
            Category::create($payload);
            $this->flash = __(':name was added.', ['name' => $validated['name']]);
        }

        $this->closeFormModal();
        $this->resetPage();
    }

    public function askDelete(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->deletingId = $category->id;
        $this->deletingLabel = $category->name;
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

        $category = Category::find($this->deletingId);

        // Products carry a non-nullable category, so removing one in use would orphan them.
        if ($category && $category->products()->exists()) {
            $this->flash = __(':name still has products assigned and cannot be deleted.', ['name' => $category->name]);
            $this->cancelDelete();

            return;
        }

        if ($category) {
            $name = $category->name;
            $category->delete();
            $this->flash = __(':name was removed.', ['name' => $name]);
        }

        $this->cancelDelete();
    }

    // ---------------------------------------------------------------
    // Active ingredients
    //
    // Active ingredients live as free text on products rather than in their
    // own table, so this tab reads the distinct values back out and renames
    // them across every product that uses them.
    // ---------------------------------------------------------------

    public function openIngredientModal(string $ingredient): void
    {
        $this->originalIngredient = $ingredient;
        $this->ingredientName = $ingredient;
        $this->resetErrorBag();
        $this->showIngredientModal = true;
    }

    public function closeIngredientModal(): void
    {
        $this->showIngredientModal = false;
        $this->originalIngredient = '';
        $this->ingredientName = '';
    }

    public function saveIngredient(): void
    {
        $validated = $this->validate([
            'ingredientName' => ['required', 'string', 'max:255'],
        ], [], ['ingredientName' => __('active ingredient')]);

        $newName = trim($validated['ingredientName']);

        if ($newName === $this->originalIngredient) {
            $this->closeIngredientModal();

            return;
        }

        $affected = Product::where('active_ingredient', $this->originalIngredient)
            ->update(['active_ingredient' => $newName]);

        $this->flash = __('Renamed to :name across :count product(s).', [
            'name' => $newName,
            'count' => $affected,
        ]);

        $this->closeIngredientModal();
        $this->resetPage();
    }

    /**
     * Distinct active ingredients with the products and categories using them.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function ingredients()
    {
        return Product::query()
            ->with('category')
            ->whereNotNull('active_ingredient')
            ->where('active_ingredient', '!=', '')
            ->when($this->search, fn ($q) => $q->where('active_ingredient', 'like', "%{$this->search}%"))
            ->get(['id', 'name', 'category_id', 'active_ingredient', 'is_restricted'])
            ->groupBy('active_ingredient')
            ->map(fn ($products, $ingredient) => [
                'name' => $ingredient,
                'product_count' => $products->count(),
                'restricted_count' => $products->where('is_restricted', true)->count(),
                'categories' => $products->pluck('category.name')->filter()->unique()->sort()->values(),
                'products' => $products->pluck('name')->sort()->values(),
            ])
            ->sortKeys()
            ->values();
    }

    public function render()
    {
        $categories = null;

        if ($this->tab === 'categories') {
            $categories = Category::query()
                ->withCount('products')
                ->when($this->search, function ($q) {
                    $q->where(function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%");
                    });
                })
                ->orderBy('name')
                ->paginate(10);
        }

        return view('livewire.categories.index', ['categories' => $categories]);
    }
}
