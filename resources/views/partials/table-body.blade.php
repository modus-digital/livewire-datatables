<tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
    @php($colors = \ModusDigital\LivewireDatatables\Support\Color::getColorMap())
    @php($baseColor = \ModusDigital\LivewireDatatables\Support\Color::get()->value)
    @forelse($rows as $row)
        <tr @class([
            'hover:bg-gray-50 dark:hover:bg-gray-700 py-4',
            $colors['background'] => $this->hasSelection() && $this->isSelected($row->getKey()),
            'cursor-pointer' => $this->hasShowRecord()
        ]) wire:key="row-{{ $row->getKey() }}" @if($this->hasShowRecord()) wire:click="showRecord(@js($row->getKey()))"
        @endif>
            @if($this->hasSelection())
                <td class="relative px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    <input type="checkbox" wire:click.stop="toggleSelection(@js($row->getKey()))"
                        @checked($this->isSelected($row->getKey()))
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded {{ 'focus:ring-' . $baseColor . '-500' }} dark:{{ 'focus:ring-' . $baseColor . '-600' }} dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                </td>
            @endif

            @foreach($this->getColumns() as $column)
                <td @class([
                    'px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300',
                    'text-' . $column->getAlign()->value
                ]) wire:key="cell-{{ $column->getField() }}-{{ $row->getKey() }}"
                    @if($column->getWidth()) style="width: {{ $column->getWidth() }}" @endif>
                    <div @class(['truncate' => $column->getWidth()]) @if($column->getWidth())
                    style="max-width: {{ $column->getWidth() }}" @endif>
                        {!! $this->renderCell($column, $row) !!}
                    </div>
                </td>
            @endforeach

            @if($this->hasRowActions())
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                    @include('livewire-datatables::partials.row-actions', ['record' => $row])
                </td>
            @endif
        </tr>
    @empty
        {{-- This will be handled by empty-state partial --}}
    @endforelse
</tbody>
