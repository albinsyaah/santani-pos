{{--
    Custom pagination for the AgroPOS design system.
    Overrides Livewire's default "tailwind" pagination view (livewire::tailwind),
    so every ->links() call in the app picks it up. Styles live in pos.css (.pg*).
--}}
@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}
$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
$pageName = $paginator->getPageName();
@endphp
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="{{ __('Pagination') }}" class="pg">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="pg-btn is-disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">
                    <x-tabler-chevron-left />
                </span>
            @else
                <button type="button" class="pg-btn" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="{{ __('Previous') }}">
                    <x-tabler-chevron-left />
                </button>
            @endif

            @foreach ($elements as $element)
                {{-- "Three dots" separator --}}
                @if (is_string($element))
                    <span class="pg-dots pg-hide-sm" aria-hidden="true">{{ $element }}</span>
                @endif

                {{-- Page numbers --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pg-btn is-active" aria-current="page" wire:key="paginator-{{ $pageName }}-page{{ $page }}">{{ $page }}</span>
                        @else
                            <button type="button" class="pg-btn pg-hide-sm" wire:key="paginator-{{ $pageName }}-page{{ $page }}"
                                    wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button type="button" class="pg-btn" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="{{ __('Next') }}">
                    <x-tabler-chevron-right />
                </button>
            @else
                <span class="pg-btn is-disabled" aria-disabled="true" aria-label="{{ __('Next') }}">
                    <x-tabler-chevron-right />
                </span>
            @endif
        </nav>
    @endif
</div>
