<div class="text-center py-8 w-full">
    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>

    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
        No data found
    </h3>

    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        @if($this->search || $this->hasActiveFilters())
            No results match your search criteria or filters.
        @else
            Get started by adding some data.
        @endif
    </p>

    @if($this->search || $this->hasActiveFilters())
        <div class="mt-4 flex justify-center space-x-3">
            @if($this->search)
                <button
                    wire:click="clearSearch"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    Clear search
                </button>
            @endif

            @if($this->hasActiveFilters())
                <button
                    wire:click="resetFilters"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    Clear filters
                </button>
            @endif
        </div>
    @endif
</div>
