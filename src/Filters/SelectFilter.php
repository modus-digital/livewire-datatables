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
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (str_contains($this->field, '.')) {
            [$relation, $field] = explode('.', $this->field, 2);

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

        $field = $this->field;
        if (! str_contains($field, '.')) {
            $field = $query->getModel()->getTable() . '.' . $field;
        }

        if ($this->multiple && is_array($value)) {
            return $query->whereIn($field, $value);
        }

        return $query->where($field, $value);
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
