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
            // Check if the field contains dot notation (indicating a relationship)
            $columnField = $column->getField();
            if (str_contains($columnField, '.')) {
                $parts = explode('.', $columnField, 2);

                return $parts[0] === $field;
            }

            return false;
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
     * Cache for attribute detection to avoid repeated reflection calls.
     *
     * @var array<string, array<string, bool>>
     */
    protected static array $attributeDetectionCache = [];

    /**
     * Check if a field is a model attribute (accessor) rather than a database column.
     * Enhanced version with improved detection and caching for performance.
     */
    protected function isModelAttribute(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        $modelClass = get_class($model);
        $cacheKey = $modelClass . '::' . $field;

        // Return cached result if available
        if (isset(static::$attributeDetectionCache[$modelClass][$field])) {
            return static::$attributeDetectionCache[$modelClass][$field];
        }

        $isAttribute = false;

        try {
            // 1. Check if it's an accessor method (old Laravel syntax)
            $accessorMethod = 'get' . \Illuminate\Support\Str::studly($field) . 'Attribute';
            if (method_exists($model, $accessorMethod)) {
                $isAttribute = true;
            }

            // 2. Check if it's defined in the model's $appends array
            if (! $isAttribute && in_array($field, $model->getAppends())) {
                $isAttribute = true;
            }

            // 3. Check if it's a cast attribute but NOT a database column
            if (! $isAttribute && array_key_exists($field, $model->getCasts())) {
                // Cast attributes can be both database columns AND computed attributes
                // We need to check if it's actually a database column
                try {
                    if (! $this->isDatabaseColumn($model, $field)) {
                        $isAttribute = true;
                    }
                } catch (\Throwable $e) {
                    // If we can't determine database columns, assume it's an attribute if it's cast
                    $isAttribute = true;
                }
            }

            // 4. Check if it's a Laravel 9+ Attribute (new syntax)
            if (! $isAttribute && method_exists($model, $field)) {
                try {
                    $reflection = new \ReflectionClass($model);
                    if ($reflection->hasMethod($field)) {
                        $method = $reflection->getMethod($field);
                        $returnType = $method->getReturnType();

                        if ($returnType instanceof \ReflectionNamedType &&
                            $returnType->getName() === 'Illuminate\Database\Eloquent\Casts\Attribute') {
                            $isAttribute = true;
                        }
                    }
                } catch (\ReflectionException $e) {
                    // Log the error but don't fail
                    \Illuminate\Support\Facades\Log::debug('Reflection error in attribute detection', [
                        'model' => $modelClass,
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 5. Check for computed attributes that use getAttribute() with custom logic
            // Disabled for now to avoid false positives - the above checks should be sufficient
            // if (!$isAttribute) {
            //     try {
            //         // First check if it's a database column - if so, it's not a computed attribute
            //         if ($this->isDatabaseColumn($model, $field)) {
            //             $isAttribute = false;
            //         } else {
            //             // Create a fresh model instance to test attribute access
            //             $testModel = new $modelClass;
            //             $testModel->exists = true; // Prevent save operations
            //
            //             // Try to access the attribute - if it exists and isn't a database column, it's computed
            //             if ($testModel->hasGetMutator($field) || $testModel->hasAttributeMutator($field)) {
            //                 $isAttribute = true;
            //             }
            //         }
            //     } catch (\Throwable $e) {
            //         // Ignore errors in this detection method - it's a fallback
            //     }
            // }

        } catch (\Throwable $e) {
            // Log error and return false as fallback
            \Illuminate\Support\Facades\Log::warning('Error in attribute detection', [
                'model' => $modelClass,
                'field' => $field,
                'error' => $e->getMessage(),
            ]);
            $isAttribute = false;
        }

        // Cache the result
        if (! isset(static::$attributeDetectionCache[$modelClass])) {
            static::$attributeDetectionCache[$modelClass] = [];
        }
        static::$attributeDetectionCache[$modelClass][$field] = $isAttribute;

        return $isAttribute;
    }

    /**
     * Check if model has specific database columns.
     *
     * @param  array<int, string>  $columns
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
        try {
            $connectionName = $model->getConnectionName();
            $tableName = $model->getTable();

            $schema = \Illuminate\Support\Facades\Schema::connection($connectionName);
            $tableColumns = $schema->getColumnListing($tableName);

            return in_array($field, $tableColumns);
        } catch (\Throwable $e) {
            // Log error and return false as fallback
            \Illuminate\Support\Facades\Log::warning('Error checking database column', [
                'model' => get_class($model),
                'field' => $field,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if a relationship field (dot notation) contains a model attribute.
     * For example: 'user.full_name' where 'full_name' is an accessor on the User model.
     */
    protected function isRelationshipAttribute(string $relationshipPath): bool
    {
        $parts = explode('.', $relationshipPath);
        if (count($parts) < 2) {
            return false;
        }

        try {
            $model = $this->getModel();
            $relationName = $parts[0];
            $relationField = $parts[1];

            // Check if the relation method exists
            if (! method_exists($model, $relationName)) {
                return false;
            }

            // Get the related model
            $relatedModel = $this->getRelatedModel($relationshipPath);
            if (! $relatedModel) {
                return false;
            }

            // Check if the field is an attribute on the related model
            return $this->isModelAttribute($relatedModel, $relationField);

        } catch (\Throwable $e) {
            // Log error and return false as fallback
            \Illuminate\Support\Facades\Log::warning('Error in relationship attribute detection', [
                'relationshipPath' => $relationshipPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enhanced method to check if a field (with or without dot notation) is a model attribute.
     * This is the main entry point for attribute detection.
     */
    public function isFieldAttribute(string $field): bool
    {
        // Handle relationship fields (dot notation)
        if (str_contains($field, '.')) {
            return $this->isRelationshipAttribute($field);
        }

        // Handle direct model attributes
        try {
            $model = $this->getModel();

            return $this->isModelAttribute($model, $field);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error in field attribute detection', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear the attribute detection cache.
     * Useful for testing or when model definitions change at runtime.
     */
    public static function clearAttributeDetectionCache(): void
    {
        static::$attributeDetectionCache = [];
    }
}
