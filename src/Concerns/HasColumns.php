<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Columns\Column;

trait HasColumns
{
    /** @var Column[] */
    protected array $columnCache = [];

    /** @var Collection<int, Column>|null */
    protected ?Collection $columnsCollection = null;

    /**
     * Define the columns for the table.
     * Override this method in your table class.
     *
     * @return Column[]
     */
    protected function columns(): array
    {
        return [];
    }

    /**
     * Get all columns.
     *
     * @return Collection<int, Column>
     */
    public function getColumns(): Collection
    {
        if (empty($this->columnCache)) {
            $this->columnCache = $this->columns();
            $this->columnsCollection = collect($this->columnCache)->filter(fn (Column $column) => ! $column->isHidden());
        }

        return $this->columnsCollection;
    }

    /**
     * Get searchable columns.
     *
     * @return Collection<int, Column>
     */
    public function getSearchableColumns(): Collection
    {
        return $this->getColumns()->filter(fn (Column $column) => $column->isSearchable());
    }

    /**
     * Get sortable columns.
     *
     * @return Collection<int, Column>
     */
    public function getSortableColumns(): Collection
    {
        return $this->getColumns()->filter(fn (Column $column) => $column->isSortable());
    }

    /**
     * Get a specific column by field name.
     */
    public function getColumn(string $field): ?Column
    {
        // First try to find by exact field match
        $column = $this->getColumns()->first(fn (Column $column) => $column->getField() === $field);

        if ($column) {
            return $column;
        }

        // For backward compatibility, check if any column has this field as a relationship
        // This supports the deprecated relationship() method
        $relationshipColumn = $this->getColumns()->first(function (Column $column) use ($field) {
            // Suppress deprecation warnings for internal compatibility check
            $originalErrorReporting = error_reporting();
            error_reporting($originalErrorReporting & ~E_USER_DEPRECATED);

            $relationship = $column->getRelationship();

            error_reporting($originalErrorReporting);

            return $relationship === $field;
        });

        if ($relationshipColumn) {
            return $relationshipColumn;
        }

        // Also check if any column's field contains dot notation matching the search field
        return $this->getColumns()->first(fn (Column $column) => $column->getField() === $field || str_contains($column->getField(), $field));
    }

    /**
     * Check if a column is sortable.
     */
    public function isColumnSortable(string $field): bool
    {
        $column = $this->getColumn($field);

        return $column && $column->isSortable();
    }

    /**
     * Get the sort field for a column (handles relationships).
     */
    public function getColumnSortField(string $field): string
    {
        $column = $this->getColumn($field);

        return $column ? $column->getSortField() : $field;
    }

    /**
     * Check if columns exist.
     */
    public function hasColumns(): bool
    {
        return $this->getColumns()->isNotEmpty();
    }

    /**
     * Render cell value for a column.
     */
    public function renderCell(Column $column, mixed $record): mixed
    {
        $value = $column->getValue($record);

        /** @var view-string|null $view */
        $view = $column->getView();

        if ($view) {
            return view($view, [
                'record' => $record,
                'value' => $value,
            ])->render();
        }

        return $value;
    }

    /**
     * Check if a field is a model attribute (accessor) rather than a database column.
     */
    protected function isModelAttribute(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        // Check if it's an accessor method (old Laravel syntax)
        $accessorMethod = 'get' . \Illuminate\Support\Str::studly($field) . 'Attribute';
        if (method_exists($model, $accessorMethod)) {
            return true;
        }

        // Check if it's defined in the model's $appends array
        if (in_array($field, $model->getAppends())) {
            return true;
        }

        // Check if it's a cast attribute
        if (array_key_exists($field, $model->getCasts())) {
            return true;
        }

        // Check if it's a Laravel 9+ Attribute (new syntax)
        if (method_exists($model, $field)) {
            $reflection = new \ReflectionClass($model);
            if ($reflection->hasMethod($field)) {
                $method = $reflection->getMethod($field);
                $returnType = $method->getReturnType();

                if ($returnType && $returnType->getName() === 'Illuminate\Database\Eloquent\Casts\Attribute') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if model has specific database columns.
     */
    protected function hasModelColumns(\Illuminate\Database\Eloquent\Model $model, array $columns): bool
    {
        $schema = \Illuminate\Support\Facades\Schema::connection($model->getConnectionName());
        $tableColumns = $schema->getColumnListing($model->getTable());

        return empty(array_diff($columns, $tableColumns));
    }

    /**
     * Get the related model for a relationship field.
     */
    protected function getRelatedModel(string $relationshipPath): ?\Illuminate\Database\Eloquent\Model
    {
        $parts = explode('.', $relationshipPath);
        if (count($parts) < 2) {
            return null;
        }

        $model = $this->getModel();
        $relationName = $parts[0];

        if (! method_exists($model, $relationName)) {
            return null;
        }

        $relation = $model->{$relationName}();

        return $relation->getRelated();
    }

    /**
     * Get the value of a model attribute dynamically.
     * This works for any Laravel model attribute (accessor, appended, cast, etc.).
     */
    protected function getModelAttributeValue(\Illuminate\Database\Eloquent\Model $model, string $attribute): mixed
    {
        return $model->getAttribute($attribute);
    }

    /**
     * Check if a field is a database column (not an attribute).
     */
    protected function isDatabaseColumn(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        $schema = \Illuminate\Support\Facades\Schema::connection($model->getConnectionName());
        $tableColumns = $schema->getColumnListing($model->getTable());

        return in_array($field, $tableColumns);
    }
}
