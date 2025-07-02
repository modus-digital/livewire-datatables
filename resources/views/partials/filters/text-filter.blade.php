<div class="relative">
    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1">
        {{ $name }}
    </label>
    <input
        type="text"
        wire:model.live.debounce.300ms="filters.{{ $field }}"
        placeholder="{{ $placeholder }}"
        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600"
    />
</div>
