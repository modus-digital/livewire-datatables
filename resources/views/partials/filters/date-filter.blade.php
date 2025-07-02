<div class="relative">
    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1">
        {{ $name }}
    </label>
    @if($isRange)
        <div class="grid grid-cols-2 gap-2">
            <input
                type="date"
                wire:model.live="filters.{{ $field }}.from"
                placeholder="From"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600"
            />
            <input
                type="date"
                wire:model.live="filters.{{ $field }}.to"
                placeholder="To"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600"
            />
        </div>
    @else
        <input
            type="date"
            wire:model.live="filters.{{ $field }}"
            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600"
        />
    @endif
</div>
