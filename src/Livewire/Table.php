<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use ModusDigital\LivewireDatatables\Concerns\HasActions;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;
use ModusDigital\LivewireDatatables\Concerns\HasFilters;
use ModusDigital\LivewireDatatables\Concerns\HasPagination;
use ModusDigital\LivewireDatatables\Concerns\HasRowActions;
use ModusDigital\LivewireDatatables\Concerns\HasRowSelection;
use ModusDigital\LivewireDatatables\Concerns\HasSorting;

abstract class Table extends Component
{
    use HasActions;
    use HasColumns;
    use HasFilters;
    use HasPagination;
    use HasRowActions;
    use HasRowSelection;
    use HasSorting;

    #[Url(as: 'search')]
    public string $search = '';

    /**
     * The model class for the table.
     */
    protected string $model = Model::class;

    /**
     * Whether to show the search input.
     */
    protected bool $searchable = true;

    /**
     * Placeholder text for the search input.
     */
    protected string $searchPlaceholder = 'Search...';

    /**
     * Get the model instance.
     */
    public function getModel(): Model
    {
        return app($this->model);
    }

    /**
     * Get the base query for the table.
     *
     * @return Builder<Model>
     */
    protected function query(): Builder
    {
        return $this->getModel()->query();
    }

    /**
     * Get the table data.
     *
     * @return Collection<int, Model>|LengthAwarePaginator<Model>
     */
    public function getRows(): Collection|LengthAwarePaginator
    {
        $query = $this->query();

        // Apply global search
        if ($this->searchable && ! empty($this->search)) {
            $query = $this->applyGlobalSearch($query);
        }

        // Apply filters
        $query = $this->applyFilters($query);

        // Apply sorting
        $query = $this->applySorting($query);

        // Return paginated results
        return $query->paginate($this->perPage);
    }

    /**
     * Apply global search across searchable columns.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applyGlobalSearch(Builder $query): Builder
    {
        if (empty($this->search)) {
            return $query;
        }

        $searchableColumns = $this->getSearchableColumns();
        if ($searchableColumns->isEmpty()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($searchableColumns) {
            foreach ($searchableColumns as $column) {
                $field = $column->getField();

                if ($column->getRelationship()) {
                    // Handle relationship search
                    $relation = explode('.', $column->getRelationship())[0];
                    $query->orWhereHas($relation, function (Builder $subQuery) use ($column) {
                        $relationField = last(explode('.', $column->getRelationship()));
                        $subQuery->where($relationField, 'like', "%{$this->search}%");
                    });
                } else {
                    if (! str_contains($field, '.')) {
                        $field = $query->getModel()->getTable() . '.' . $field;
                    }

                    $query->orWhere($field, 'like', "%{$this->search}%");
                }
            }
        });
    }

    /**
     * Check if search is enabled.
     */
    public function isSearchable(): bool
    {
        return $this->searchable && $this->getSearchableColumns()->isNotEmpty();
    }

    /**
     * Get search placeholder.
     */
    public function getSearchPlaceholder(): string
    {
        return $this->searchPlaceholder;
    }

    /**
     * Clear search.
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Hook for when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Component mount hook.
     */
    public function mount(): void
    {
        $this->initializeFilters();
        $this->initializeSorting();
    }

    /**
     * Determine if the table has an override for the row click handler.
     */
    protected function hasShowRecord(): bool
    {
        $method = new \ReflectionMethod($this, 'showRecord');

        return $method->getDeclaringClass()->getName() !== self::class;
    }

    /**
     * Handle clicking on a row. Override in your table component.
     *
     * Typical implementations may redirect to a route or dispatch a Livewire
     * event with the selected record ID.
     */
    public function showRecord(string|int $id): void
    {
        // Override in your table to define row click behaviour.
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        /** @var view-string $view */
        $view = 'livewire-datatables::table';

        return view($view, [
            'rows' => $this->getRows(),
        ]);
    }
}
