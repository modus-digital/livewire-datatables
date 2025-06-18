<div class="flex items-center justify-between gap-4 space-y-4">
    <div class="flex items-center gap-2">

        @if($this->isSearchable())
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $this->getSearchPlaceholder() }}"
                class="ml-4 my-4 w-md bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            />

            @if($search)
                <button
                    wire:click="clearSearch"
                    class="inline-flex items-center p-3 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span class="mx-auto">Clear</span>
                </button>
            @endif
        @endif

        @if($this->hasActiveFilters())
            <div class="flex items-end gap-2">
                <button
                    wire:click="resetFilters"
                    class="min-w-[105px] inline-flex items-center p-3 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span class="mx-auto">Reset Filters</span>
                </button>
            </div>
        @endif
    </div>

    <div class="flex items-center gap-2">
        @foreach($this->getActions() as $action)
            <button
                wire:click="executeAction('{{ $action->getKey() }}')"
                @if($action->getConfirmMessage()) onclick="return confirm('{{ $action->getConfirmMessage() }}')" @endif
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md',
                    'bg-indigo-600 hover:bg-indigo-700 text-white' => !$action->getClass(),
                    $action->getClass() => $action->getClass()
                ])
            >
                @if($action->getIcon())
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        {!! $action->getIcon() !!}
                    </svg>
                @endif
                {{ $action->getLabel() }}
            </button>
        @endforeach
    </div>
</div>
