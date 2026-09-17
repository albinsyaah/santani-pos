<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Categories & Active Ingredients') }}</h1>
            <p>{{ __('Manage product categories and the active ingredients used in agricultural chemicals.') }}</p>
        </div>
        @if ($tab === 'categories')
            <button class="btn btn-primary" wire:click="openCreateModal">
                <x-tabler-plus stroke-width="2.5" />
                {{ __('Add category') }}
            </button>
        @endif
    </div>

    @if ($flash)
        <div class="toast" style="position:static;margin-bottom:16px;display:inline-flex;">
            <x-tabler-check />
            {{ $flash }}
        </div>
    @endif

    <div class="tab-bar">
        <button type="button" class="tab-btn {{ $tab === 'categories' ? 'active' : '' }}" wire:click="switchTab('categories')">
            <x-tabler-box />
            {{ __('Categories') }}
        </button>
        <button type="button" class="tab-btn {{ $tab === 'ingredients' ? 'active' : '' }}" wire:click="switchTab('ingredients')">
            <x-tabler-leaf />
            {{ __('Active ingredients') }}
        </button>
    </div>

    @if ($tab === 'categories')
        <div class="card">
            <div class="card-body" style="padding-bottom:0;">
                <div class="toolbar">
                    <div class="toolbar-filters">
                        <input type="text" class="input" placeholder="{{ __('Search category…') }}" wire:model.live.debounce.300ms="search">
                    </div>
                    <span class="cell-muted">{{ __(':count categor(ies)', ['count' => $categories->total()]) }}</span>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Products') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}">
                                <td>
                                    <p class="cell-strong">{{ $category->name }}</p>
                                    @if ($category->description)
                                        <p class="cell-muted">{{ $category->description }}</p>
                                    @endif
                                </td>
                                <td class="cell-muted">{{ $category->slug }}</td>
                                <td>
                                    <span class="badge {{ $category->products_count > 0 ? 'badge-lime' : 'badge-neutral' }}">
                                        {{ __(':count product(s)', ['count' => $category->products_count]) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;justify-content:flex-end;">
                                        <button class="kebab" wire:click="openEditModal({{ $category->id }})" aria-label="{{ __('Edit') }}">
                                            <x-tabler-edit />
                                        </button>
                                        <button class="kebab" wire:click="askDelete({{ $category->id }})" aria-label="{{ __('Delete') }}">
                                            <x-tabler-trash />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="table-empty">{{ __('No categories match your search yet.') }}</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                <span>{{ __('Page :current of :last', ['current' => $categories->currentPage(), 'last' => $categories->lastPage()]) }}</span>
                {{ $categories->links() }}
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body" style="padding-bottom:0;">
                <div class="toolbar">
                    <div class="toolbar-filters">
                        <input type="text" class="input" placeholder="{{ __('Search active ingredient…') }}" wire:model.live.debounce.300ms="search">
                    </div>
                    <span class="cell-muted">{{ __(':count ingredient(s)', ['count' => $this->ingredients->count()]) }}</span>
                </div>
            </div>

            <div class="card-body" style="padding-top:0;">
                <p class="stat-caption" style="margin-bottom:12px;">
                    {{ __('Active ingredients are recorded on each product. Renaming one here updates every product that uses it.') }}
                </p>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Active ingredient') }}</th>
                            <th>{{ __('Used in') }}</th>
                            <th>{{ __('Categories') }}</th>
                            <th>{{ __('Restricted') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->ingredients as $ingredient)
                            <tr wire:key="ingredient-{{ md5($ingredient['name']) }}">
                                <td>
                                    <p class="cell-strong">{{ $ingredient['name'] }}</p>
                                    <p class="cell-muted">{{ $ingredient['products']->take(3)->implode(', ') }}@if ($ingredient['products']->count() > 3), …@endif</p>
                                </td>
                                <td>
                                    <span class="badge badge-lime">{{ __(':count product(s)', ['count' => $ingredient['product_count']]) }}</span>
                                </td>
                                <td class="cell-muted">{{ $ingredient['categories']->implode(', ') ?: '—' }}</td>
                                <td>
                                    @if ($ingredient['restricted_count'] > 0)
                                        <span class="badge badge-warn">{{ __(':count restricted', ['count' => $ingredient['restricted_count']]) }}</span>
                                    @else
                                        <span class="badge badge-neutral">{{ __('Standard') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;justify-content:flex-end;">
                                        <button class="kebab" wire:click="openIngredientModal(@js($ingredient['name']))" aria-label="{{ __('Rename') }}">
                                            <x-tabler-edit />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <p class="table-empty">{{ __('No active ingredients recorded yet. Add one from a product in Product Data.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($showFormModal)
        <div class="modal-backdrop" wire:click.self="closeFormModal">
            <div class="modal-panel" style="max-width:520px;">
                <div class="modal-head">
                    <h3>{{ $editingId ? __('Edit category') : __('Add category') }}</h3>
                    <button class="kebab" wire:click="closeFormModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Category name') }}</label>
                            <input type="text" class="input @error('name') invalid @enderror" wire:model="name" placeholder="{{ __('e.g. Herbicides') }}">
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label>{{ __('Slug') }} <span class="hint">({{ __('optional') }})</span></label>
                            <input type="text" class="input @error('slug') invalid @enderror" wire:model="slug" placeholder="{{ __('Generated from the name if left empty') }}">
                            @error('slug') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label>{{ __('Description') }} <span class="hint">({{ __('optional') }})</span></label>
                            <textarea class="textarea @error('description') invalid @enderror" wire:model="description" placeholder="{{ __('What belongs in this category?') }}"></textarea>
                            @error('description') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeFormModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Save changes') : __('Save category') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showIngredientModal)
        <div class="modal-backdrop" wire:click.self="closeIngredientModal">
            <div class="modal-panel" style="max-width:520px;">
                <div class="modal-head">
                    <h3>{{ __('Rename active ingredient') }}</h3>
                    <button class="kebab" wire:click="closeIngredientModal" aria-label="{{ __('Close') }}">
                        <x-tabler-x />
                    </button>
                </div>
                <form wire:submit="saveIngredient">
                    <div class="modal-body">
                        <div class="field">
                            <label>{{ __('Active ingredient') }}</label>
                            <input type="text" class="input @error('ingredientName') invalid @enderror" wire:model="ingredientName" placeholder="{{ __('e.g. Glyphosate 480 SL') }}">
                            @error('ingredientName') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <p class="stat-caption">
                            {{ __('Every product currently set to ":name" will be updated. Typing the name of another ingredient merges the two.', ['name' => $originalIngredient]) }}
                        </p>
                    </div>
                    <div class="modal-foot">
                        <button type="button" class="btn" wire:click="closeIngredientModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveIngredient">
                            <span wire:loading.remove wire:target="saveIngredient">{{ __('Save changes') }}</span>
                            <span wire:loading wire:target="saveIngredient">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete-modal')
</div>
