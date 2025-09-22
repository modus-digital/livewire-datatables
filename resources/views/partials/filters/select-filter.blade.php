@props(['field', 'placeholder' => null, 'name', 'hideLabel' => false, 'multiple' => false, 'options' => []])

@php
    $normalizedOptions = collect($options)
        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
        ->values();
@endphp

@php($colors = \ModusDigital\LivewireDatatables\Support\Color::getColorMap())

<div class="relative" x-data="{
    open: false,
    query: '',
    multiple: {{ $multiple ? 'true' : 'false' }},
    options: @js($normalizedOptions),
    field: @js($field),
    selected: null,
    init() {
        const readFromWire = () => {
            const parts = String(this.field).split('.');
            let obj = $wire.filters ?? {};
            for (const key of parts) {
                if (obj == null) return null;
                obj = obj[key];
            }
            return obj;
        };

        const initial = readFromWire();
        if (this.multiple) {
            if (!Array.isArray(initial)) {
                this.selected = initial ? [String(initial)] : [];
            } else {
                this.selected = initial.map(v => String(v));
            }
        } else {
            if (Array.isArray(initial)) {
                this.selected = initial[0] ? String(initial[0]) : '';
            } else {
                this.selected = (initial ?? '') === null ? '' : String(initial ?? '');
            }
        }

        this.$watch('selected', (v) => {
            const valueToSend = this.multiple ? (Array.isArray(v) ? v : []) : v;
            $wire.set('filters.' + this.field, valueToSend);
        });

        window.addEventListener('filters-cleared', () => {
            this.selected = this.multiple ? [] : '';
        });
        window.addEventListener('filter-cleared', (e) => {
            if (e.detail && e.detail.field === this.field) {
                this.selected = this.multiple ? [] : '';
            }
        });
    },
    filteredOptions() {
        const q = this.query.toLowerCase().trim();
        if (!q) return this.options;
        return this.options.filter(o => String(o.label).toLowerCase().includes(q));
    },
    isSelected(val) {
        const v = String(val);
        return Array.isArray(this.selected) ? this.selected.includes(v) : String(this.selected) === v;
    },
    toggleOption(val) {
        const v = String(val);
        if (this.multiple) {
            const arr = Array.isArray(this.selected) ? [...this.selected] : [];
            const i = arr.indexOf(v);
            if (i === -1) arr.push(v); else arr.splice(i, 1);
            this.selected = arr;
        } else {
            this.selected = this.isSelected(v) ? '' : v;
            this.open = false;
        }
    },
    selectedLabels() {
        if (this.multiple) {
            const map = new Map(this.options.map(o => [o.value, o.label]));
            return (Array.isArray(this.selected) ? this.selected : []).map(v => map.get(String(v)) ?? v);
        }
        const map = new Map(this.options.map(o => [o.value, o.label]));
        return this.selected ? [map.get(String(this.selected)) ?? this.selected] : [];
    },
    clearAll() {
        this.selected = this.multiple ? [] : '';
    },
    selectedCount() {
        if (!this.multiple) return 0;
        return Array.isArray(this.selected) ? this.selected.length : 0;
    }
}" x-on:click.outside="open = false" x-cloak>
    @unless ($hideLabel)
        <label class="block text-base font-medium leading-6 text-gray-900 dark:text-white mb-1">
            {{ $name }}
        </label>
    @endunless

    <button type="button"
        class="w-full inline-flex items-center justify-between gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
        x-on:click="open = !open">
        <div class="flex flex-wrap items-center gap-1">
            <template x-if="selectedLabels().length === 0">
                <span class="text-gray-400 dark:text-gray-400">{{ $placeholder ?: $name }}</span>
            </template>
            <template x-for="(label, idx) in selectedLabels()" :key="label + '-' + idx">
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs {{ $colors['background'] }} {{ $colors['text'] }}">
                    <span x-text="label"></span>
                    <template x-if="multiple">
                        <button type="button" class="{{ $colors['text'] }}"
                            x-on:click.stop="toggleOption(selected[idx])">
                            &times;
                        </button>
                    </template>
                </span>
            </template>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="multiple && selectedCount() > 0">
                <span
                    class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-medium {{ $colors['background'] }} text-white"
                    x-text="selectedCount()"></span>
            </template>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </button>

    <div class="absolute z-50 mt-1 w-full rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg"
        x-show="open" x-transition>
        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
            <input type="text" x-model="query" placeholder="Zoeken..."
                class="w-full rounded-md border-0 py-1.5 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm dark:ring-gray-600 dark:bg-gray-700 dark:text-gray-200" />
        </div>
        <div class="max-h-60 overflow-auto py-1">
            @if (!$multiple)
                <button type="button"
                    class="w-full text-left px-3 py-2 text-sm text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700"
                    x-on:click="clearAll()">-- {{ $placeholder ?: $name }} --</button>
            @endif
            <template x-for="opt in filteredOptions()" :key="opt.value">
                <button type="button"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                    x-on:click="toggleOption(opt.value)">
                    <span x-text="opt.label"></span>
                    <span class="ml-2" x-show="isSelected(opt.value)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $colors['text'] }}"
                            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.364a1 1 0 0 1-1.437 0L3.29 9.03a1 1 0 1 1 1.42-1.408l3.03 3.05 6.54-6.625a1 1 0 0 1 1.424.244Z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </template>
        </div>
        <div class="p-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between"
            x-show="multiple">
            <button type="button" class="text-xs text-gray-600 dark:text-gray-300 hover:underline"
                x-on:click="clearAll()">Alles wissen</button>
            <button type="button" class="text-xs {{ $colors['text'] }} hover:underline"
                x-on:click="open = false">Gereed</button>
        </div>
    </div>
</div>
