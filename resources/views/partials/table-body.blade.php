<tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
    @forelse($rows as $row)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 @if($this->hasSelection() && $this->isSelected($row->id)) bg-indigo-50 dark:bg-indigo-900/50 @endif">
            {{-- Selection Checkbox --}}
            @if($this->hasSelection())
                <td class="relative px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    <input
                        type="checkbox"
                        wire:click="toggleSelection({{ $row->id }})"
                        @checked($this->isSelected($row->id))
                        class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                    />
                </td>
            @endif

            {{-- Data Columns --}}
            @foreach($this->getColumns() as $column)
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 @if($column->getAlign()) text-{{ $column->getAlign() }} @endif">
                    <div class="@if($column->getWidth()) max-w-0 truncate @endif">
                        {!! $this->renderCell($column, $row) !!}
                    </div>
                </td>
            @endforeach

            {{-- Row Actions --}}
            @if($this->hasRowActions())
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    @include('livewire.tables.partials.row-actions', ['record' => $row])
                </td>
            @endif
        </tr>
    @empty
        {{-- This will be handled by empty-state partial --}}
    @endforelse
</tbody>
