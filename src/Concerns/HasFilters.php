<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Database\Eloquent\Builder;
use ModusDigital\LivewireDatatables\Filters\Filter;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;

trait HasFilters
{
    #[Url(as: 'filter')]
    public array $filters = [];

    /** @var Filter[] */
    protected array $filterCache = [];

    /**
     * Define the filters for the table.
     * Override this method in your table class.
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Get all filters.
     */
    public function getFilters(): Collection
    {
        if (empty($this->filterCache)) {
            $this->filterCache = $this->filters();
        }

        return collect($this->filterCache);
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(Builder $query): Builder
    {
        foreach ($this->getFilters() as $filter) {
            $value = $this->filters[$filter->getField()] ?? null;

            if ($value !== null && $value !== '' && $value !== []) {
                $query = $filter->apply($query, $value);
            }
        }

        return $query;
    }

    /**
     * Reset all filters.
     */
    public function resetFilters(): void
    {
        $this->filters = [];
        $this->resetPage();
    }

    /**
     * Reset a specific filter.
     */
    public function resetFilter(string $field): void
    {
        unset($this->filters[$field]);
        $this->resetPage();
    }

    /**
     * Check if any filters are active.
     */
    public function hasActiveFilters(): bool
    {
        return !empty(array_filter($this->filters, fn($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Get active filter count.
     */
    public function getActiveFilterCount(): int
    {
        return count(array_filter($this->filters, fn($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Initialize filter default values.
     */
    public function initializeFilters(): void
    {
        foreach ($this->getFilters() as $filter) {
            $field = $filter->getField();
            if (!isset($this->filters[$field]) && $filter->getDefault() !== null) {
                $this->filters[$field] = $filter->getDefault();
            }
        }
    }

    /**
     * Hook for when filters are updated.
     */
    public function updatedFilters(): void
    {
        $this->resetPage();
    }
}
