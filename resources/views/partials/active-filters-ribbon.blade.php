@if(config('livewire-datatables.filters.ribbon') && method_exists($this, 'hasActiveFilters') && $this->hasActiveFilters())
@php $activeFilters = []; @endphp
@foreach($this->getFilters() as $filter)
    @php
        $field = $filter->getField();
        $value = data_get($this->filters, $field);
        if ($value === null || $value === '' || $value === []) {
            $display = null;
        } else {
            $label = $filter->getName();
            $display = '';
            if ($filter instanceof \ModusDigital\LivewireDatatables\Filters\SelectFilter) {
                $options = $filter->getOptions();
                if (is_array($value)) {
                    $display = implode(', ', array_map(fn($v) => $options[$v] ?? (string) $v, $value));
                } else {
                    $display = $options[$value] ?? (string) $value;
                }
            } elseif ($filter instanceof \ModusDigital\LivewireDatatables\Filters\DateFilter) {
                if ($filter->isRange() && is_array($value)) {
                    $from = $value['from'] ?? null;
                    $to = $value['to'] ?? null;
                    if ($from && $to) {
                        $display = $from . ' – ' . $to;
                    } elseif ($from) {
                        $display = 'from ' . $from;
                    } elseif ($to) {
                        $display = 'to ' . $to;
                    }
                } else {
                    $display = is_string($value) ? $value : '';
                }
            } else {
                $display = is_array($value) ? implode(', ', $value) : (string) $value;
            }
            if ($display !== '') {
                $activeFilters[] = ['label' => $label, 'value' => $display, 'field' => $field];
            }
        }
    @endphp
@endforeach

@php($colors = \ModusDigital\LivewireDatatables\Support\Color::getColorMap())

@if(!empty($activeFilters))
    <div class="mt-2 border-t border-gray-200 dark:border-gray-700 py-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-gray-600 dark:text-gray-300 ml-6">Active filters:</span>
            @foreach($activeFilters as $af)
                <span
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs {{ $colors['background'] }} {{ $colors['text'] }}">
                    <span class="font-medium">{{ $af['label'] }}:</span>
                    <span>{{ $af['value'] }}</span>
                    <button type="button" class="ml-1 text-sm font-medium cursor-pointer {{ $colors['text'] }}"
                        wire:click="resetFilter('{{ $af['field'] }}')" aria-label="Remove filter">&times;</button>
                </span>
            @endforeach
        </div>
    </div>
@endif
@endif
