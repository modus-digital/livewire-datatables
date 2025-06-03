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
                    class="inline-flex items-center p-3 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span class="mx-auto">Clear</span>
                </button>
            @endif
        @endif

        @if($this->hasActiveFilters())
            <div class="flex items-end gap-2">
                <button
                    wire:click="resetFilters"
                    class="min-w-[105px] inline-flex items-center p-3 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span class="mx-auto">Reset Filters</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Global actions slot --}}
    <div class="flex items-center gap-2">
        @if(isset($actions))
            <div class="flex items-center space-x-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
