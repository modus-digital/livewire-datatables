<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DateFilter extends Filter
{
    protected bool $range = false;

    protected string $format = 'Y-m-d';

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

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if ($this->range && is_array($value)) {
            if (! empty($value['from'])) {
                $query->where($this->field, '>=', Carbon::parse($value['from'])->startOfDay());
            }
            if (! empty($value['to'])) {
                $query->where($this->field, '<=', Carbon::parse($value['to'])->endOfDay());
            }

            return $query;
        }

        return $query->whereDate($this->field, Carbon::parse($value));
    }

    public function render(): string
    {
        $inputClass = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600';

        if ($this->range) {
            return "
                <div class=\"relative\">
                    <label class=\"block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1\">{$this->name}</label>
                    <div class=\"grid grid-cols-2 gap-2\">
                        <input
                            type=\"date\"
                            wire:model.live=\"filters.{$this->field}.from\"
                            placeholder=\"From\"
                            class=\"{$inputClass}\"
                        />
                        <input
                            type=\"date\"
                            wire:model.live=\"filters.{$this->field}.to\"
                            placeholder=\"To\"
                            class=\"{$inputClass}\"
                        />
                    </div>
                </div>
            ";
        }

        return "
            <div class=\"relative\">
                <label class=\"block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-1\">{$this->name}</label>
                <input
                    type=\"date\"
                    wire:model.live=\"filters.{$this->field}\"
                    class=\"{$inputClass}\"
                />
            </div>
        ";
    }
}
