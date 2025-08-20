<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DateFilter extends Filter
{
    protected bool $range = false;

    protected string $format = 'Y-m-d';

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

    public function range(bool $range = true): self
    {
        $this->range = $range;

        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function isRange(): bool
    {
        return $this->range;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        // Reset attribute filtering state
        $this->requiresAttributeFiltering = false;
        $this->attributeFilterDetails = [];

        if (empty($value)) {
            return $query;
        }

        // Handle relationship fields with dot notation
        if (str_contains($this->field, '.')) {
            return $this->applyRelationshipFilter($query, $value);
        }

        // Check if this is a model attribute before applying SQL filtering
        $model = $query->getModel();
        if ($this->isFieldAttribute($model, $this->field)) {
            return $this->applyDirectAttributeFilter($query, $this->field, $value);
        }

        // Apply regular SQL filtering for database columns
        $field = $this->field;
        if (! str_contains($field, '.')) {
            $field = $model->getTable() . '.' . $field;
        }

        if ($this->range && is_array($value)) {
            if (! empty($value['from'])) {
                $query->where($field, '>=', Carbon::parse($value['from'])->startOfDay());
            }
            if (! empty($value['to'])) {
                $query->where($field, '<=', Carbon::parse($value['to'])->endOfDay());
            }

            return $query;
        }

        return $query->whereDate($field, Carbon::parse($value));
    }

    /**
     * Apply filter to a direct model attribute.
     */
    protected function applyDirectAttributeFilter(Builder $query, string $attributeField, mixed $value): Builder
    {
        // Set flag to indicate that attribute filtering is needed
        $this->requiresAttributeFiltering = true;

        // Store the filtering details for later use
        $this->attributeFilterDetails = [
            'relation' => null, // No relation for direct attributes
            'field' => $attributeField,
            'value' => $value,
            'type' => 'date',
            'range' => $this->range,
            'filter_field' => $this->field,
        ];

        // Return the query unchanged - the Table class will handle the filtering
        return $query;
    }

    /**
     * Apply filter to a relationship field, handling model attributes.
     */
    protected function applyRelationshipFilter(Builder $query, mixed $value): Builder
    {
        [$relation, $field] = explode('.', $this->field, 2);

        // Check if we need to get the related model to detect attributes
        $model = $query->getModel();
        if (method_exists($model, $relation)) {
            $relationInstance = $model->{$relation}();
            $relatedModel = $relationInstance->getRelated();

            // Check if the field is a model attribute using enhanced detection
            if ($this->isFieldAttribute($relatedModel, $field)) {
                return $this->applyAttributeFilter($query, $relation, $field, $relatedModel, $value);
            }
        }

        // Handle regular field filtering
        if ($this->range && is_array($value)) {
            return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
                if (! empty($value['from'])) {
                    $q->where($field, '>=', Carbon::parse($value['from'])->startOfDay());
                }
                if (! empty($value['to'])) {
                    $q->where($field, '<=', Carbon::parse($value['to'])->endOfDay());
                }
            });
        }

        return $query->whereHas($relation, fn (Builder $q) => $q->whereDate($field, Carbon::parse($value)));
    }

    /**
     * Apply filter to a model attribute in a relationship.
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
            'type' => 'date',
            'range' => $this->range,
            'filter_field' => $this->field,
        ];

        // Return the query unchanged - the Table class will handle the filtering
        return $query;
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

    public function render(): string
    {
        return view('livewire-datatables::partials.filters.date-filter', [
            'name' => $this->name,
            'field' => $this->field,
            'isRange' => $this->range,
        ])->render();
    }
}
