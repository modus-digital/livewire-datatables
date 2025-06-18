<thead class="bg-gray-50 dark:bg-gray-800 mx-4">
    <tr>
        @if ($this->hasSelection())
            <th scope="col" class="relative px-6 py-3 text-left">
                <input
                    type="checkbox"
                    wire:model.live="selectAll"
                    wire:click="toggleSelectAll"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-xs focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                />
            </th>
        @endif

        {{-- Column headers --}}
        @foreach($this->getColumns() as $column)
            <th
                scope="col"
                @class([
                    'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800' => $column->isSortable(),
                    'px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400'
                ])
                @if($column->isSortable()) wire:click="sortBy('{{ $column->getField() }}')" @endif
                @if($column->getWidth()) style="width: {{ $column->getWidth() }}" @endif
            >
                <div class="flex items-center gap-2">
                    @if($column->getAlign() === 'left')
                        <span class="text-left">
                            {{ $column->getName() }}
                        </span>
                    @elseif($column->getAlign() === 'center')
                        <span class="text-center">
                            {{ $column->getName() }}
                        </span>
                    @elseif($column->getAlign() === 'right')
                        <span class="text-right">
                            {{ $column->getName() }}
                        </span>
                    @else
                        <span>
                            {{ $column->getName() }}
                        </span>
                    @endif

                    @if($column->isSortable())
                        <span class="ml-2">
                            @if($this->isSorted($column->getField()))
                                @if($this->sortDirection === 'asc')
                                    <svg class="h-4 w-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            @else
                                <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>
        @endforeach

        @if($this->hasRowActions())
            <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400 w-6">
                <span class="sr-only">Actions</span>
            </th>
        @endif
    </tr>
</thead>
