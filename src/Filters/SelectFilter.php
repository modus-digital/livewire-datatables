<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends Filter
{
    protected array $options = [];

    protected bool $multiple = false;

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

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (str_contains($this->field, '.')) {
            [$relation, $field] = explode('.', $this->field, 2);
            return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
                if ($this->multiple && is_array($value)) {
                    $q->whereIn($field, $value);
                } else {
                    $q->where($field, $value);
                }
            });
        }

        if ($this->multiple && is_array($value)) {
            return $query->whereIn($this->field, $value);
        }

        return $query->where($this->field, $value);
    }

    public function render(): string
    {
        $selectClass = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600';

        $multiple = $this->multiple ? 'multiple' : '';
        $placeholder = $this->placeholder ?? "Select {$this->name}";

        $options = '';
        if (! $this->multiple) {
            $options .= "<option value=\"\">{$placeholder}</option>";
        }

        foreach ($this->options as $value => $label) {
            $options .= "<option value=\"{$value}\">{$label}</option>";
        }

        return "
            <div class=\"relative\">
                <label class=\"block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1\">{$this->name}</label>
                <select
                    wire:model.live=\"filters.{$this->field}\"
                    class=\"{$selectClass}\"
                    {$multiple}
                >
                    {$options}
                </select>
            </div>
        ";
    }
}
