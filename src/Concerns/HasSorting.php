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

    /** @var array<string, bool> */
    protected array $joinedTables = [];

    /**
     * Flag to indicate if current sorting requires attribute-based sorting.
     */
    protected bool $requiresAttributeSorting = false;

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
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function applySorting(Builder $query): Builder
    {
        // Reset the attribute sorting flag
        $this->requiresAttributeSorting = false;

        $sortField = $this->sortField ?: $this->defaultSortField;
        $sortDirection = $this->sortDirection ?: $this->defaultSortDirection;

        $column = $this->getColumn($sortField);

        // Handle custom sort callback
        if ($column && $column->hasSortCallback()) {
            $callback = $column->getSortCallback();

            return $callback($query, $sortDirection);
        }

        $relationship = null;
        if ($column) {
            $sortField = $column->getSortField();
            $relationship = $column->getRelationship();
        }

        if (! $relationship && str_contains($sortField, '.')) {
            $relationship = $sortField;
        }

        if ($relationship) {
            return $this->applySortingWithRelationship($query, $relationship, $sortDirection);
        }

        if (! str_contains($sortField, '.')) {
            $sortField = $query->getModel()->getTable() . '.' . $sortField;
        }

        return $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Apply sorting with relationship handling.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySortingWithRelationship(Builder $query, string $relationship, string $sortDirection): Builder
    {
        $parts = explode('.', $relationship);

        if (count($parts) === 2) {
            [$relationName, $relationField] = $parts;
            $model = $this->getModel();

            // Check if the relation method exists
            if (! method_exists($model, $relationName)) {
                return $query;
            }

            $relationInstance = $model->{$relationName}();
            $relationTable = $relationInstance->getRelated()->getTable();

            // Check if the field is a model attribute instead of a database column
            if ($this->isModelAttribute($relationInstance->getRelated(), $relationField)) {
                return $this->applySortingWithAttribute($query, $relationInstance, $relationField, $sortDirection);
            }

            // Only add JOIN if it hasn't been added already
            if (! isset($this->joinedTables[$relationTable])) {
                if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                    $foreignKey = $relationInstance->getForeignKeyName();
                    $ownerKey = $relationInstance->getOwnerKeyName();
                    $query->leftJoin($relationTable, $model->getTable() . '.' . $foreignKey, '=', $relationTable . '.' . $ownerKey);
                } elseif ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasOne || $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                    $foreignKey = $relationInstance->getForeignKeyName();
                    $localKey = $relationInstance->getLocalKeyName();
                    $query->leftJoin($relationTable, $relationTable . '.' . $foreignKey, '=', $model->getTable() . '.' . $localKey);
                }
                $this->joinedTables[$relationTable] = true;
            }

            $query->orderBy("{$relationTable}.{$relationField}", $sortDirection)
                ->select($model->getTable() . '.*');

            return $query;
        }

        return $query;
    }

    /**
     * Apply sorting for model attributes.
     * Since attributes are computed in PHP, we can't sort them in SQL.
     * We'll set a flag to indicate that attribute sorting is needed.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySortingWithAttribute(Builder $query, \Illuminate\Database\Eloquent\Relations\Relation $relationInstance, string $attributeField, string $sortDirection): Builder
    {
        // Set flag to indicate that attribute sorting is needed
        $this->requiresAttributeSorting = true;

        // For model attributes, we can't sort in SQL since they're computed in PHP
        // We return the query unchanged - the Table class will handle the sorting
        return $query;
    }

    /**
     * Check if current sorting requires attribute-based sorting.
     */
    public function requiresAttributeSorting(): bool
    {
        return $this->requiresAttributeSorting;
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
