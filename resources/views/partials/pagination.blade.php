<div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
    <div class="flex items-center justify-between">
        {{-- Results info --}}
        <div class="flex-1 flex justify-between sm:hidden">
            @if($rows->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                    Previous
                </span>
            @else
                <button
                    wire:click="previousPage"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Previous
                </button>
            @endif

            @if($rows->hasMorePages())
                <button
                    wire:click="nextPage"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Next
                </button>
            @else
                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            {{-- Results count --}}
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing
                    <span class="font-medium">{{ $rows->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-medium">{{ $rows->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-medium">{{ $rows->total() }}</span>
                    results
                </p>
            </div>

            {{-- Pagination links --}}
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if($rows->onFirstPage())
                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @else
                        <button
                            wire:click="previousPage"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-hidden focus:ring-1 focus:ring-indigo-500"
                        >
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif

                    {{-- Smart Pagination Elements --}}
                    @php
                        $currentPage = $rows->currentPage();
                        $lastPage = $rows->lastPage();
                        $maxVisible = 6;
                    @endphp

                    @if($lastPage <= $maxVisible)
                        {{-- Show all pages if total pages <= 6 --}}
                        @for($page = 1; $page <= $lastPage; $page++)
                            @if($page == $currentPage)
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600 dark:bg-indigo-900 dark:border-indigo-700 dark:text-indigo-200">
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    wire:click="gotoPage({{ $page }})"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        @endfor
                    @else
                        {{-- Smart pagination with ellipses --}}
                        @if($currentPage <= 4)
                            {{-- Current page is near the beginning --}}
                            @for($page = 1; $page <= 5; $page++)
                                @if($page == $currentPage)
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600 dark:bg-indigo-900 dark:border-indigo-700 dark:text-indigo-200">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button
                                        wire:click="gotoPage({{ $page }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            @endfor

                            {{-- Ellipsis --}}
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                ...
                            </span>

                            {{-- Last page --}}
                            <button
                                wire:click="gotoPage({{ $lastPage }})"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                {{ $lastPage }}
                            </button>

                        @elseif($currentPage >= $lastPage - 3)
                            {{-- Current page is near the end --}}
                            {{-- First page --}}
                            <button
                                wire:click="gotoPage(1)"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                1
                            </button>

                            {{-- Ellipsis --}}
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                ...
                            </span>

                            {{-- Last 5 pages --}}
                            @for($page = $lastPage - 4; $page <= $lastPage; $page++)
                                @if($page == $currentPage)
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600 dark:bg-indigo-900 dark:border-indigo-700 dark:text-indigo-200">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button
                                        wire:click="gotoPage({{ $page }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            @endfor

                        @else
                            {{-- Current page is in the middle --}}
                            {{-- First page --}}
                            <button
                                wire:click="gotoPage(1)"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                1
                            </button>

                            {{-- First ellipsis --}}
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                ...
                            </span>

                            {{-- Current page and surrounding pages --}}
                            @for($page = $currentPage - 1; $page <= $currentPage + 1; $page++)
                                @if($page == $currentPage)
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600 dark:bg-indigo-900 dark:border-indigo-700 dark:text-indigo-200">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button
                                        wire:click="gotoPage({{ $page }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            @endfor

                            {{-- Second ellipsis --}}
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                ...
                            </span>

                            {{-- Last page --}}
                            <button
                                wire:click="gotoPage({{ $lastPage }})"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                {{ $lastPage }}
                            </button>
                        @endif
                    @endif

                    {{-- Next Page Link --}}
                    @if($rows->hasMorePages())
                        <button
                            wire:click="nextPage"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                        >
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @else
                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-default dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>
