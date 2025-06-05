<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

trait HasSorting
{
    #[Url(as: 'sort')]
    public string $sortField = '';

    #[Url(as: 'dir')]
    public string $sortDirection = 'asc';

    /**
     * Default sort field.
     */
    protected string $defaultSortField = 'id';

    /**
     * Default sort direction.
     */
    protected string $defaultSortDirection = 'asc';

    /**
     * Sort by a column.
     */
    public function sortBy(string $field): void
    {
        if (! $this->isColumnSortable($field)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Apply sorting to the query.
     */
    protected function applySorting(Builder $query): Builder
    {
        $sortField = $this->sortField ?: $this->defaultSortField;
        $sortDirection = $this->sortDirection ?: $this->defaultSortDirection;

        // Handle relationship sorting
        if ($column = $this->getColumn($sortField)) {
            $sortField = $column->getSortField();

            // If it's a relationship, we need to join the table
            if ($column->getRelationship()) {
                $relation = $column->getRelationship();
                $parts = explode('.', $relation);

                if (count($parts) === 2) {
                    [$relationName, $relationField] = $parts;
                    $model = $this->getModel();
                    $relationInstance = $model->{$relationName}();
                    $relationTable = $relationInstance->getRelated()->getTable();

                    if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $foreignKey = $relationInstance->getForeignKeyName();
                        $ownerKey = $relationInstance->getOwnerKeyName();
                        $query->leftJoin($relationTable, $model->getTable().'.'.$foreignKey, '=', $relationTable.'.'.$ownerKey);
                    } elseif ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasOne || $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                        $foreignKey = $relationInstance->getForeignKeyName();
                        $localKey = $relationInstance->getLocalKeyName();
                        $query->leftJoin($relationTable, $relationTable.'.'.$foreignKey, '=', $model->getTable().'.'.$localKey);
                    }

                    $query->orderBy("{$relationTable}.{$relationField}", $sortDirection)
                        ->select($model->getTable().'.*');

                    return $query;
                }
            }
        }

        return $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Get the sort icon for a column.
     */
    public function getSortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'sort';
        }

        return $this->sortDirection === 'asc' ? 'sort-asc' : 'sort-desc';
    }

    /**
     * Check if a column is currently being sorted.
     */
    public function isSorted(string $field): bool
    {
        return $this->sortField === $field;
    }

    /**
     * Initialize default sorting.
     */
    public function initializeSorting(): void
    {
        if (empty($this->sortField)) {
            $this->sortField = $this->defaultSortField;
            $this->sortDirection = $this->defaultSortDirection;
        }
    }
}
