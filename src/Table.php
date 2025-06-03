<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables;

// Laravel
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\Collection;
use Livewire\{Component, Attributes\Url};

// Concerns
use ModusDigital\LivewireDatatables\Concerns\HasColumns;
use ModusDigital\LivewireDatatables\Concerns\HasFilters;
use ModusDigital\LivewireDatatables\Concerns\HasPagination;
use ModusDigital\LivewireDatatables\Concerns\HasRowActions;
use ModusDigital\LivewireDatatables\Concerns\HasRowSelection;
use ModusDigital\LivewireDatatables\Concerns\HasSorting;

abstract class Table extends Component
{
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
     */
    protected function query(): Builder
    {
        return $this->getModel()->query();
    }

    /**
     * Get the table data.
     */
    public function getRows(): Collection|LengthAwarePaginator
    {
        $query = $this->query();

        // Apply global search
        if ($this->searchable && !empty($this->search)) {
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
     * Render the component.
     */
    public function render()
    {
        return view('livewire-datatables::table', [
            'rows' => $this->getRows(),
        ]);
    }
}
