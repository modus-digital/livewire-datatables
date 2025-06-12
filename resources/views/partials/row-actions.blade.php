@if($this->getRecordActions($record)->isNotEmpty())
    <div x-data="{ actionsOpen: false }" class="relative inline-block text-right" x-cloak>
        {{-- Actions Button --}}
        <button
            @click="actionsOpen = !actionsOpen"
            class="inline-flex cursor-pointer items-center p-2 text-gray-400 bg-white rounded-full hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-300"
        >
            <span class="sr-only">Open options</span>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
            </svg>
        </button>

        {{-- Actions Dropdown --}}
        <div
            x-show="actionsOpen"
            @click.away="actionsOpen = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 z-10 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700"
        >
            <div class="py-1">
                @foreach($this->getRecordActions($record) as $action)
                    <button
                        wire:click="executeRowAction('{{ $action->getKey() }}', {{ $record->id }})"
                        @if($action->getConfirmMessage())
                            onclick="return confirm('{{ $action->getConfirmMessage() }}')"
                        @endif
                        class="flex items-center w-full px-4 py-2 text-sm {{ $action->getClass() }} hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        @if($action->getIcon())
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                {!! $action->getIcon() !!}
                            </svg>
                        @endif
                        {{ $action->getLabel() }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endif
