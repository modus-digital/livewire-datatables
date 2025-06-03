<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg w-full">

    {{-- Start of Header --}}
    @include('livewire-datatables::partials.header')

    <div class="my-2 border-b border-gray-200 dark:border-gray-700"></div>
    {{-- End of Header --}}

    {{-- Start of Table content --}}
    <div class="overflow-x-auto">
        {{-- Start of filters --}}
        <div>
            @include('livewire-datatables::partials.filters')
        </div>
        {{-- End of filters --}}

        <div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Table Head --}}
                @include('livewire-datatables::partials.table-head')

                {{-- Table Body --}}
                @include('livewire-datatables::partials.table-body')
            </table>
        </div>
    </div>
    {{-- End of Table content --}}

    {{-- Start of Empty State and Pagination --}}
    @if($rows->isEmpty())
        @include('livewire-datatables::partials.empty-state')
    @endif

    {{-- Pagination --}}
    @if($rows->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            @include('livewire-datatables::partials.pagination')
        </div>
    @endif
    {{-- End of Empty State and Pagination --}}

</div>
