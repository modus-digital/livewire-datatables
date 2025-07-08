<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends Filter
{
    /** @var array<string|int, string> */
    protected array $options = [];

    protected bool $multiple = false;

    /**
     * Flag to indicate if current filtering requires attribute-based filtering.
     */
    protected bool $requiresAttributeFiltering = false;

    /**
     * Store the attribute filtering details for later use.
     */
    protected array $attributeFilterDetails = [];

    /**
     * @param  array<string|int, string>  $options
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function multiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Check if current filtering requires attribute-based filtering.
     */
    public function requiresAttributeFiltering(): bool
    {
        return $this->requiresAttributeFiltering;
    }

    /**
     * Get the attribute filtering details.
     */
    public function getAttributeFilterDetails(): array
    {
        return $this->attributeFilterDetails;
    }

    /**
     * Reset the attribute filtering state.
     */
    public function resetAttributeFiltering(): void
    {
        $this->requiresAttributeFiltering = false;
        $this->attributeFilterDetails = [];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        // Reset attribute filtering state
        $this->resetAttributeFiltering();

        if (empty($value)) {
            return $query;
        }

        if (str_contains($this->field, '.')) {
            return $this->applyRelationshipFilter($query, $value);
        }

        $field = $this->field;
        if (! str_contains($field, '.')) {
            $field = $query->getModel()->getTable() . '.' . $field;
        }

        if ($this->multiple && is_array($value)) {
            return $query->whereIn($field, $value);
        }

        return $query->where($field, $value);
    }

    /**
     * Apply filter to a relationship field, handling model attributes.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyRelationshipFilter(Builder $query, mixed $value): Builder
    {
        $parts = explode('.', $this->field, 2);
        [$relation, $field] = $parts;

        // Check if we need to get the related model to detect attributes
        $model = $query->getModel();
        if (method_exists($model, $relation)) {
            $relationInstance = $model->{$relation}();
            $relatedModel = $relationInstance->getRelated();

            // Check if the field is a model attribute
            if ($this->isModelAttribute($relatedModel, $field)) {
                return $this->applyAttributeFilter($query, $relation, $field, $relatedModel, $value);
            }
        }

        // Handle regular field filtering
        return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
            $relatedTable = $q->getModel()->getTable();
            $qualifiedField = $relatedTable . '.' . $field;

            if ($this->multiple && is_array($value)) {
                $q->whereIn($qualifiedField, $value);
            } else {
                $q->where($qualifiedField, $value);
            }
        });
    }

    /**
     * Apply filter to a model attribute.
     * Set flag to indicate that attribute filtering is needed.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyAttributeFilter(Builder $query, string $relation, string $attributeField, \Illuminate\Database\Eloquent\Model $relatedModel, mixed $value): Builder
    {
        // Set flag to indicate that attribute filtering is needed
        $this->requiresAttributeFiltering = true;

        // Store the filtering details for later use
        $this->attributeFilterDetails = [
            'relation' => $relation,
            'field' => $attributeField,
            'value' => $value,
            'multiple' => $this->multiple,
            'filter_field' => $this->field,
        ];

        // Return the query unchanged - the Table class will handle the filtering
        return $query;
    }

    /**
     * Check if a field is a model attribute (accessor) rather than a database column.
     */
    protected function isModelAttribute(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        // Check if it's an accessor method
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

    public function render(): string
    {
        $placeholder = $this->placeholder ?? "Select {$this->name}";

        return view('livewire-datatables::partials.filters.select-filter', [
            'name' => $this->name,
            'field' => $this->field,
            'placeholder' => $placeholder,
            'options' => $this->options,
            'multiple' => $this->multiple,
        ])->render();
    }
}
