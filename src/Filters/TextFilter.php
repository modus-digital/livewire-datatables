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
            [$relation, $field] = explode('.', $this->field, 2);

            return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
                match ($this->operator) {
                    '=' => $q->where($field, '=', $value),
                    'starts_with' => $q->where($field, 'like', "{$value}%"),
                    'ends_with' => $q->where($field, 'like', "%{$value}"),
                    default => $q->where($field, 'like', "%{$value}%"),
                };
            });
        }

        return match ($this->operator) {
            '=' => $query->where($this->field, '=', $value),
            'like' => $query->where($this->field, 'like', "%{$value}%"),
            'starts_with' => $query->where($this->field, 'like', "{$value}%"),
            'ends_with' => $query->where($this->field, 'like', "%{$value}"),
            default => $query->where($this->field, 'like', "%{$value}%"),
        };
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
