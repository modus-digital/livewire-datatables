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
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function getRows(): Collection|LengthAwarePaginator
    {
        $query = $this->query();

        // Apply global search
        if ($this->searchable && ! empty($this->search)) {
            $query = $this->applyGlobalSearch($query);
        }

        // Apply filters first to determine if attribute filtering is needed
        $query = $this->applyFilters($query);

        // Apply sorting to determine if attribute sorting is needed
        $query = $this->applySorting($query);

        // Check if we need to handle attributes in PHP
        $needsAttributeSorting = $this->requiresAttributeSorting();
        $needsAttributeFiltering = $this->requiresAttributeFiltering();

        if ($needsAttributeSorting || $needsAttributeFiltering) {
            // For attribute operations, we need to get all results and process in PHP
            $results = $query->get();

            // Apply attribute filtering if needed
            if ($needsAttributeFiltering) {
                $results = $this->filterByAttributes($results);
            }

            // Apply attribute sorting if needed
            if ($needsAttributeSorting) {
                $results = $this->sortByAttribute($results);
            }

            // Manual pagination for attribute operations
            return $this->paginateCollection($results);
        } else {
            // Return paginated results (sorting and filtering already applied)
            return $query->paginate($this->perPage);
        }
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
                $query->orWhere(function ($q) use ($column) {
                    // Check if the field contains dot notation (indicating a relationship)
                    $columnField = $column->getField();
                    $hasRelationship = str_contains($columnField, '.');

                    if ($hasRelationship) {
                        // Handle relationship search with attribute detection
                        $this->applySearchToRelationship($q, $column);
                    } else {
                        // Handle regular field search
                        $field = $column->getField();
                        if (! str_contains($field, '.')) {
                            $field = $q->getModel()->getTable() . '.' . $field;
                        }
                        $q->where($field, 'like', "%{$this->search}%");
                    }
                });
            }
        });
    }

    /**
     * Apply search to a relationship field, handling model attributes.
     *
     * @param  Builder<Model>  $query
     */
    protected function applySearchToRelationship(Builder $query, \ModusDigital\LivewireDatatables\Columns\Column $column): void
    {
        // Get the relationship path from the field (using dot notation)
        $relationshipPath = $column->getField();

        // If field doesn't contain dot notation, it's not a relationship
        if (! str_contains($relationshipPath, '.')) {
            return;
        }

        $parts = explode('.', $relationshipPath);

        if (count($parts) < 2) {
            return;
        }

        $relation = $parts[0];
        $relationField = $parts[1];

        // Get the related model to check if the field is an attribute
        $relatedModel = $this->getRelatedModel($relationshipPath);

        if ($relatedModel && $this->isModelAttribute($relatedModel, $relationField)) {
            // Handle attribute search
            $this->applyAttributeSearch($query, $relation, $relationField, $relatedModel);
        } else {
            // Handle regular field search
            $query->orWhereHas($relation, function (Builder $subQuery) use ($relationField) {
                $subQuery->where($relationField, 'like', "%{$this->search}%");
            });
        }
    }

    /**
     * Apply search to a model attribute.
     *
     * @param  Builder<Model>  $query
     */
    protected function applyAttributeSearch(Builder $query, string $relation, string $attributeField, \Illuminate\Database\Eloquent\Model $relatedModel): void
    {
        // For model attributes, we can't reliably search in SQL
        // We'll fall back to a best-effort approach using whereHas
        $query->orWhereHas($relation, function (Builder $subQuery) {
            // This is a limitation - we can't easily search model attributes in SQL
            // The search will be less precise for attributes
            $subQuery->where(function (Builder $innerQuery) {
                // Get the table name for the related model
                $table = $innerQuery->getModel()->getTable();

                // Try common field patterns that might be used in attributes
                $commonFields = ['name', 'title', 'description', 'first_name', 'last_name'];
                foreach ($commonFields as $field) {
                    if ($innerQuery->getModel()->getConnection()->getSchemaBuilder()->hasColumn($table, $field)) {
                        $innerQuery->orWhere("{$table}.{$field}", 'like', "%{$this->search}%");
                    }
                }
            });
        });
    }

    /**
     * Check if current sorting requires attribute sorting (PHP-based).
     * This method is now a wrapper around the HasSorting trait's requiresAttributeSorting method.
     */
    protected function needsAttributeSorting(): bool
    {
        // Apply sorting to determine if attribute sorting is needed
        $query = $this->query();
        $this->applySorting($query);

        return $this->requiresAttributeSorting();
    }

    /**
     * Filter a collection by model attributes.
     *
     * @param  Collection<int, Model>  $collection
     * @return Collection<int, Model>
     */
    protected function filterByAttributes(Collection $collection): Collection
    {
        $attributeFilters = $this->getActiveAttributeFilters();

        if (empty($attributeFilters)) {
            return $collection;
        }

        return $collection->filter(function ($model) use ($attributeFilters) {
            foreach ($attributeFilters as $filterDetails) {
                $relationName = $filterDetails['relation'];
                $attributeField = $filterDetails['field'];
                $filterValue = $filterDetails['value'];
                $operator = $filterDetails['operator'] ?? 'like';
                $multiple = $filterDetails['multiple'] ?? false;

                $relatedModel = $model->{$relationName};
                if (! $relatedModel) {
                    return false;
                }

                $attributeValue = $this->getModelAttributeValue($relatedModel, $attributeField);

                // Apply the filter based on operator
                if (! $this->matchesAttributeFilter($attributeValue, $filterValue, $operator, $multiple)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Check if an attribute value matches the filter criteria.
     */
    protected function matchesAttributeFilter(mixed $attributeValue, mixed $filterValue, string $operator, bool $multiple): bool
    {
        if ($attributeValue === null) {
            return false;
        }

        $attributeString = (string) $attributeValue;

        if ($multiple && is_array($filterValue)) {
            return in_array($attributeValue, $filterValue);
        }

        return match ($operator) {
            '=' => $attributeString === (string) $filterValue,
            'like' => str_contains(strtolower($attributeString), strtolower((string) $filterValue)),
            'starts_with' => str_starts_with(strtolower($attributeString), strtolower((string) $filterValue)),
            'ends_with' => str_ends_with(strtolower($attributeString), strtolower((string) $filterValue)),
            default => str_contains(strtolower($attributeString), strtolower((string) $filterValue)),
        };
    }

    /**
     * Sort a collection by a model attribute.
     *
     * @param  Collection<int, Model>  $collection
     * @return Collection<int, Model>
     */
    protected function sortByAttribute(Collection $collection): Collection
    {
        if (empty($this->sortField)) {
            return $collection;
        }

        $parts = explode('.', $this->sortField);
        if (count($parts) !== 2) {
            return $collection;
        }

        [$relationName, $relationField] = $parts;
        $sortDirection = $this->sortDirection === 'desc' ? SORT_DESC : SORT_ASC;

        return $collection->sortBy(function ($model) use ($relationName, $relationField) {
            $relatedModel = $model->{$relationName};
            if (! $relatedModel) {
                return null;
            }

            return $this->getModelAttributeValue($relatedModel, $relationField);
        }, SORT_REGULAR, $sortDirection === SORT_DESC);
    }

    /**
     * Manually paginate a collection.
     *
     * @param  Collection<int, Model>  $collection
     * @return LengthAwarePaginator<int, Model>
     */
    protected function paginateCollection(Collection $collection): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $perPage = $this->perPage;
        $offset = ($page - 1) * $perPage;

        $items = $collection->slice($offset, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
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
