<div class="px-6 py-4 my-6 dark:bg-gray-900 border-gray-200 dark:border-gray-700">
    <div class="flex flex-wrap gap-4 items-end py-4">
        @foreach($this->getFilters() as $filter)
            <div class="flex-1 min-w-[200px]">
                {!! $filter->render() !!}
            </div>
        @endforeach

        @if($this->hasActiveFilters())
            <div class="flex items-end gap-2">
                <button
                    wire:click="resetFilters"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Reset Filters
                </button>
            </div>
        @endif
    </div>
</div>
