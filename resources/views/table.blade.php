<div @class(['bg-white dark:bg-gray-800 shadow-sm rounded-lg w-full', 'overflow-hidden' => config('livewire-datatables.filters.style') !== 'popup'])>
    @if($this->isSearchable() || !empty($actions) || count($this->getFilters()))
        @include('livewire-datatables::partials.header')
    @endif

    @include('livewire-datatables::partials.active-filters-ribbon')

    @if($this->isSearchable() || !empty($actions))
        <div class="border-b border-gray-200 dark:border-gray-700"></div>
    @endif

    @if(count($this->getFilters()) && config('livewire-datatables.filters.style') !== 'popup')
        <div @class(['px-6 py-4 mb-4', 'overflow-x-auto' => config('livewire-datatables.filters.style') !== 'popup'])>
            @include('livewire-datatables::partials.filters')
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            @include('livewire-datatables::partials.table-head')

            @include('livewire-datatables::partials.table-body')
        </table>
    </div>

    @if($rows->isEmpty())
        @include('livewire-datatables::partials.empty-state')
    @endif

    @if($rows->hasPages())
        @include('livewire-datatables::partials.pagination')
    @endif

</div>

@once
    <script>
        (function () {
            var alpineReady = false;
            function markAlpineReady() { alpineReady = true; }
            window.addEventListener('alpine:init', markAlpineReady, { once: true });
            window.addEventListener('alpine:initialized', markAlpineReady, { once: true });
            window.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (!alpineReady && !(window.Alpine && window.Alpine.version)) {
                        console.warn('Alpine.js is missing or not initialized.');
                    }
                }, 800);
            });
        })();
    </script>
@endonce
