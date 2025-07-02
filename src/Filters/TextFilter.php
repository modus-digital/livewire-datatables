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
                    '=' => $q->where($field, $value),
                    'starts_with' => $q->where($field, 'like', "{$value}%"),
                    'ends_with' => $q->where($field, 'like', "%{$value}"),
                    default => $q->where($field, 'like', "%{$value}%"),
                };
            });
        }

        return match ($this->operator) {
            '=' => $query->where($this->field, $value),
            'like' => $query->where($this->field, 'like', "%{$value}%"),
            'starts_with' => $query->where($this->field, 'like', "{$value}%"),
            'ends_with' => $query->where($this->field, 'like', "%{$value}"),
            default => $query->where($this->field, 'like', "%{$value}%"),
        };
    }

    public function render(): string
    {
        $inputClass = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600';
        $placeholder = $this->placeholder ?? "Filter by {$this->name}";

        return "
            <div class=\"relative\">
                <label class=\"block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1\">
                    {$this->name}
                </label>
                <input
                    type=\"text\"
                    wire:model.live.debounce.300ms=\"filters.{$this->field}\"
                    placeholder=\"{$placeholder}\"
                    class=\"{$inputClass}\"
                />
            </div>
        ";
    }
}
