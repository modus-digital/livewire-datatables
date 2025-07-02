<div class="relative">
    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1">
        {{ $name }}
    </label>
    <select
        wire:model.live="filters.{{ $field }}"
        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600"
        @if($multiple) multiple @endif
    >
        @if(!$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
