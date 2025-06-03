<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg w-full">
    @include('livewire-datatables::partials.header')

    @if($this->isSearchable() || !empty($actions))
        <div class="border-b border-gray-200 dark:border-gray-700 mx-4"></div>
    @endif

    <div class="px-6 py-4 mb-4 overflow-x-auto">
        @include('livewire-datatables::partials.filters')
    </div>

    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        @include('livewire-datatables::partials.table-head')

        @include('livewire-datatables::partials.table-body')
    </table>

    @if($rows->isEmpty())
        @include('livewire-datatables::partials.empty-state')
    @endif

    @if($rows->hasPages())
        @include('livewire-datatables::partials.pagination')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</div>
