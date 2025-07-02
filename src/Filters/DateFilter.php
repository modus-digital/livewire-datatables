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

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if ($this->range && is_array($value)) {
            if (str_contains($this->field, '.')) {
                [$relation, $field] = explode('.', $this->field, 2);

                return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
                    if (! empty($value['from'])) {
                        $q->where($field, '>=', Carbon::parse($value['from'])->startOfDay());
                    }
                    if (! empty($value['to'])) {
                        $q->where($field, '<=', Carbon::parse($value['to'])->endOfDay());
                    }
                });
            }

            if (! empty($value['from'])) {
                $query->where($this->field, '>=', Carbon::parse($value['from'])->startOfDay());
            }
            if (! empty($value['to'])) {
                $query->where($this->field, '<=', Carbon::parse($value['to'])->endOfDay());
            }

            return $query;
        }

        if (str_contains($this->field, '.')) {
            [$relation, $field] = explode('.', $this->field, 2);

            return $query->whereHas($relation, fn (Builder $q) => $q->whereDate($field, Carbon::parse($value)));
        }

        return $query->whereDate($this->field, Carbon::parse($value));
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
