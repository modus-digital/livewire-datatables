@props(['field', 'placeholder' => null, 'name', 'hideLabel' => false, 'isRange' => false])

<div class="relative" x-data="{
    open: false,
    field: @js($field),
    isRange: @js($isRange),
    query: '',

    // Calendar view state
    viewYear: (new Date()).getFullYear(),
    viewMonth: (new Date()).getMonth(), // 0-11

    // Selection state
    selectedSingle: '',
    selectedFrom: '',
    selectedTo: '',

    init() {
        // Initialize from Livewire
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
        if (this.isRange) {
            this.selectedFrom = initial && initial.from ? this.coerceDateString(initial.from) : '';
            this.selectedTo = initial && initial.to ? this.coerceDateString(initial.to) : '';
            const ref = this.parseISO(this.selectedFrom) || new Date();
            this.viewYear = ref.getFullYear();
            this.viewMonth = ref.getMonth();
        } else {
            this.selectedSingle = initial ? this.coerceDateString(initial) : '';
            const ref = this.parseISO(this.selectedSingle) || new Date();
            this.viewYear = ref.getFullYear();
            this.viewMonth = ref.getMonth();
        }

        // Keep calendar in sync when opening
        this.$watch('open', (val) => {
            if (val) {
                const ref = this.isRange
                    ? (this.parseISO(this.selectedFrom) || new Date())
                    : (this.parseISO(this.selectedSingle) || new Date());
                this.viewYear = ref.getFullYear();
                this.viewMonth = ref.getMonth();
            }
        });

        window.addEventListener('filters-cleared', () => {
            this.selectedSingle = '';
            this.selectedFrom = '';
            this.selectedTo = '';
        });
        window.addEventListener('filter-cleared', (e) => {
            if (e.detail && e.detail.field === this.field) {
                this.selectedSingle = '';
                this.selectedFrom = '';
                this.selectedTo = '';
            }
        });
    },

    // Utils
    pad(n) { return n < 10 ? '0' + n : '' + n; },
    formatISO(date) { return date ? `${date.getFullYear()}-${this.pad(date.getMonth()+1)}-${this.pad(date.getDate())}` : ''; },
    parseISO(str) {
        if (!str) return null;
        const m = String(str).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return null;
        const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
        return isNaN(d.getTime()) ? null : d;
    },
    coerceDateString(val) {
        // Accept various inputs and coerce to YYYY-MM-DD
        const d = this.parseISO(String(val));
        return d ? this.formatISO(d) : '';
    },

    monthLabel(m) {
        return [
            'January','February','March','April','May','June','July','August','September','October','November','December'
        ][m];
    },

    daysInMonth(year, month) {
        return new Date(year, month + 1, 0).getDate();
    },

    firstDayOfMonth(year, month) {
        return new Date(year, month, 1).getDay(); // 0=Sun ... 6=Sat
    },

    prevMonth() {
        if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; } else { this.viewMonth--; }
    },
    nextMonth() {
        if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; } else { this.viewMonth++; }
    },

    selectDate(dateStr) {
        if (this.isRange) {
            if (!this.selectedFrom || (this.selectedFrom && this.selectedTo)) {
                this.selectedFrom = dateStr;
                this.selectedTo = '';
            } else {
                // Ensure from <= to
                if (this.compareISO(dateStr, this.selectedFrom) < 0) {
                    this.selectedTo = this.selectedFrom;
                    this.selectedFrom = dateStr;
                } else {
                    this.selectedTo = dateStr;
                }
            }
            $wire.set('filters.' + this.field, { from: this.selectedFrom || null, to: this.selectedTo || null });
        } else {
            this.selectedSingle = dateStr;
            $wire.set('filters.' + this.field, this.selectedSingle);
            this.open = false;
        }
    },

    compareISO(a, b) {
        if (!a && !b) return 0;
        if (!a) return -1; if (!b) return 1;
        return a.localeCompare(b);
    },

    isSameDayStr(a, b) { return a && b && a === b; },
    isBetween(dateStr, startStr, endStr) {
        if (!startStr || !endStr) return false;
        return startStr <= dateStr && dateStr <= endStr;
    },

    clear() {
        if (this.isRange) {
            this.selectedFrom = '';
            this.selectedTo = '';
            $wire.set('filters.' + this.field, { from: null, to: null });
        } else {
            this.selectedSingle = '';
            $wire.set('filters.' + this.field, '');
        }
    },

    calendarDays() {
        const firstDow = this.firstDayOfMonth(this.viewYear, this.viewMonth);
        const days = this.daysInMonth(this.viewYear, this.viewMonth);
        const blanks = Array.from({ length: (firstDow + 6) % 7 });
        const grid = blanks.map(() => null);
        for (let d = 1; d <= days; d++) {
            const date = new Date(this.viewYear, this.viewMonth, d);
            grid.push({ label: d, iso: this.formatISO(date) });
        }
        return grid;
    }
}" x-on:keydown.escape.window="open = false" x-cloak>
    @unless ($hideLabel)
        <label class="block text-base font-medium leading-6 text-gray-900 dark:text-white mb-1">
            {{ $name }}
        </label>
    @endunless

    <button type="button"
        class="w-full inline-flex items-center justify-between gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
        x-on:click="open = !open">
        <div class="flex flex-wrap items-center gap-2">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
            <template x-if="!isRange && !selectedSingle">
                <span class="text-gray-400 dark:text-gray-400">{{ $placeholder ?: 'Select date' }}</span>
            </template>
            <template x-if="!isRange && selectedSingle">
                <span x-text="selectedSingle"></span>
            </template>

            <template x-if="isRange && !selectedFrom && !selectedTo">
                <span class="text-gray-400 dark:text-gray-400">{{ $placeholder ?: 'Select date range' }}</span>
            </template>
            <template x-if="isRange && (selectedFrom || selectedTo)">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 text-xs"
                        x-show="selectedFrom" x-text="selectedFrom"></span>
                    <span class="text-gray-400">–</span>
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 text-xs"
                        x-show="selectedTo" x-text="selectedTo"></span>
                </div>
            </template>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"
            aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div class="absolute z-50 mt-2 w-[min(90vw,360px)] max-w-[90vw] right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3"
        x-show="open" x-transition x-on:click.outside="open = false">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div class="flex items-center gap-2">
                <button type="button" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-on:click="prevMonth()" aria-label="Previous month">
                    <svg class="w-4 h-4 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.79 14.3a.75.75 0 0 1-1.08 1.04l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.08 1.04L8.58 10l4.21 4.3Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <select x-model.number="viewMonth"
                        class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-1.5 py-1 text-sm text-gray-700 dark:text-gray-200">
                        <template
                            x-for="(m, idx) in ['January','February','March','April','May','June','July','August','September','October','November','December']"
                            :key="idx">
                            <option :value="idx" x-text="m"></option>
                        </template>
                    </select>
                    <input type="number" x-model.number="viewYear"
                        class="w-20 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-1.5 py-1 text-sm text-gray-700 dark:text-gray-200" />
                </div>
                <button type="button" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-on:click="nextMonth()" aria-label="Next month">
                    <svg class="w-4 h-4 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.21 5.7a.75.75 0 0 1 1.08-1.04l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.08-1.04L11.42 10 7.21 5.7Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="text-xs text-gray-600 dark:text-gray-300 hover:underline"
                    x-on:click="clear()">Clear</button>
                <button type="button" class="text-xs text-indigo-600 hover:underline" x-on:click="open=false"
                    x-show="!isRange">Done</button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-600 dark:text-gray-200 mb-1">
            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
        </div>
        <div class="grid grid-cols-7 gap-1">
            <template x-for="cell in calendarDays()" :key="cell ? cell.iso : Math.random()">
                <div>
                    <template x-if="cell === null">
                        <div class="h-8"></div>
                    </template>
                    <template x-if="cell !== null">
                        <button type="button" class="w-full h-8 rounded text-sm
                                   text-gray-700 dark:text-gray-100
                                   hover:bg-gray-100 dark:hover:bg-gray-700
                                  " :class="{
                                'bg-indigo-600 text-white': (!isRange && isSameDayStr(cell.iso, selectedSingle)),
                                'bg-indigo-600/90 text-white': (isRange && (isSameDayStr(cell.iso, selectedFrom) || isSameDayStr(cell.iso, selectedTo))),
                                'bg-indigo-500/20 text-indigo-900 dark:text-indigo-200': (isRange && isBetween(cell.iso, selectedFrom, selectedTo) && !isSameDayStr(cell.iso, selectedFrom) && !isSameDayStr(cell.iso, selectedTo)),
                            }" x-text="cell.label" x-on:click="selectDate(cell.iso)"></button>
                    </template>
                </div>
            </template>
        </div>

        <template x-if="isRange">
            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 dark:text-gray-300">From</span>
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 text-xs min-w-[6rem] text-center"
                        x-text="selectedFrom || '—'"></span>
                    <span class="text-xs text-gray-600 dark:text-gray-300">To</span>
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 text-xs min-w-[6rem] text-center"
                        x-text="selectedTo || '—'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="text-xs text-indigo-600 hover:underline"
                        x-on:click="open=false">Apply</button>
                </div>
            </div>
        </template>
    </div>
</div>
