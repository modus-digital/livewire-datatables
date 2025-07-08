<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

class TextFilter extends Filter
{
    protected string $operator = 'like';

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
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function apply(Builder $query, mixed $value): Builder
    {
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
     * Since we can't reliably filter model attributes in SQL, this is a best-effort approach.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyAttributeFilter(Builder $query, string $relation, string $attributeField, \Illuminate\Database\Eloquent\Model $relatedModel, mixed $value): Builder
    {
        // For model attributes, we can't reliably filter in SQL
        // We'll fall back to a best-effort approach using common field patterns
        return $query->whereHas($relation, function (Builder $subQuery) use ($value) {
            // Try common field patterns that might be used in attributes
            $commonFields = ['name', 'title', 'description', 'first_name', 'last_name'];
            $subQuery->where(function (Builder $q) use ($commonFields, $value) {
                foreach ($commonFields as $field) {
                    if ($q->getModel()->getConnection()->getSchemaBuilder()->hasColumn($q->getModel()->getTable(), $field)) {
                        $searchValue = match ($this->operator) {
                            '=' => $value,
                            'starts_with' => "{$value}%",
                            'ends_with' => "%{$value}",
                            default => "%{$value}%",
                        };

                        if ($this->operator === '=') {
                            $q->orWhere($field, '=', $searchValue);
                        } else {
                            $q->orWhere($field, 'like', $searchValue);
                        }
                    }
                }
            });
        });
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
