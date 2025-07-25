<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

class TextFilter extends Filter
{
    protected string $operator = 'like';

    /**
     * Flag to indicate if current filtering requires attribute-based filtering.
     */
    protected bool $requiresAttributeFiltering = false;

    /**
     * Store the attribute filtering details for later use.
     *
     * @var array<string, mixed>
     */
    protected array $attributeFilterDetails = [];

    public function exact(): self
    {
        $this->operator = '=';

        return $this;
    }

    public function contains(): self
    {
        $this->operator = 'like';

        return $this;
    }

    public function startsWith(): self
    {
        $this->operator = 'starts_with';

        return $this;
    }

    public function endsWith(): self
    {
        $this->operator = 'ends_with';

        return $this;
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
     *
     * @return array<string, mixed>
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

        return match ($this->operator) {
            '=' => $query->where($field, '=', $value),
            'like' => $query->where($field, 'like', "%{$value}%"),
            'starts_with' => $query->where($field, 'like', "{$value}%"),
            'ends_with' => $query->where($field, 'like', "%{$value}"),
            default => $query->where($field, 'like', "%{$value}%"),
        };
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
            match ($this->operator) {
                '=' => $q->where($field, '=', $value),
                'starts_with' => $q->where($field, 'like', "{$value}%"),
                'ends_with' => $q->where($field, 'like', "%{$value}"),
                default => $q->where($field, 'like', "%{$value}%"),
            };
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
            'operator' => $this->operator,
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

                if ($returnType instanceof \ReflectionNamedType && $returnType->getName() === 'Illuminate\Database\Eloquent\Casts\Attribute') {
                    return true;
                }
            }
        }

        return false;
    }

    public function render(): string
    {
        $placeholder = $this->placeholder ?? "Filter by {$this->name}";

        return view('livewire-datatables::partials.filters.text-filter', [
            'name' => $this->name,
            'field' => $this->field,
            'placeholder' => $placeholder,
        ])->render();
    }
}
