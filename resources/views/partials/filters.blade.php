@if(config('livewire-datatables.filters.style') === 'popup')
    <div class="relative z-[9999] flex justify-end">
        <details class="relative">
            @php
                $activeFilterCount = method_exists($this, 'getActiveFilterCount') ? $this->getActiveFilterCount() : 0;
            @endphp
            <summary
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer select-none hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700 mr-4 my-4"
                style="list-style: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M3 4.75A.75.75 0 0 1 3.75 4h12.5a.75.75 0 0 1 .6 1.2l-4.6 6.133a1 1 0 0 0-.2.6v2.817a.75.75 0 0 1-1.09.67l-2-1A.75.75 0 0 1 8.75 13.8v-2.067a1 1 0 0 0-.2-.6L3.85 5.2A.75.75 0 0 1 3 4.75Z"
                        clip-rule="evenodd" />
                </svg>
                <span>Filters</span>
                @if($activeFilterCount > 0)
                    <span
                        class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-medium bg-indigo-600 text-white">
                        {{ $activeFilterCount }}
                    </span>
                @endif
            </summary>
            <div class="absolute z-50 mt-2 w-[min(90vw,640px)] max-w-[90vw] right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4"
                wire:ignore>
                <div class="flex flex-wrap gap-4 items-end">
                    @foreach($this->getFilters() as $filter)
                        <div class="flex-1 min-w-[200px]">
                            {!! $filter->render() !!}
                        </div>
                    @endforeach
                </div>
                @if($this->hasActiveFilters())
                    <div class="mt-3 text-right">
                        <a href="#" wire:click.prevent="resetFilters" class="text-xs text-indigo-600 hover:underline">Clear
                            filters</a>
                    </div>
                @endif
            </div>
        </details>
    </div>
@else
    <div class="flex flex-wrap gap-4 items-end">
        @foreach($this->getFilters() as $filter)
            <div class="flex-1 min-w-[200px]">
                {!! $filter->render() !!}
            </div>
        @endforeach
    </div>
@endif
