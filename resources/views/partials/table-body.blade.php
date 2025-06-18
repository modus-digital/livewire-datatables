<tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
    @forelse($rows as $row)
        <tr
            @class([
                'hover:bg-gray-50 dark:hover:bg-gray-700 py-4',
                'bg-indigo-50 dark:bg-indigo-900/50' => $this->hasSelection() && $this->isSelected($row->id)
            ])
            wire:key="row-{{ $row->id }}"
        >
            @if($this->hasSelection())
                <td class="relative px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    <input
                        type="checkbox"
                        wire:click="toggleSelection({{ $row->id }})"
                        @checked($this->isSelected($row->id))
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-xs focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                </td>
            @endif

            @foreach($this->getColumns() as $column)
                <td
                    @class([
                        'px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300',
                        'text-' . $column->getAlign() => $column->getAlign()
                    ])
                    wire:key="cell-{{ $column->getField() }}-{{ $row->id }}"
                >
                    <div @class(['max-w-0 truncate' => $column->getWidth()])>
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
