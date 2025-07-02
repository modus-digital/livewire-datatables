<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use ModusDigital\LivewireDatatables\Filters\Filter;

trait HasFilters
{
    #[Url(as: 'filter')]
    /** @var array<string, mixed> */
    public array $filters = [];

    /** @var Filter[] */
    protected array $filterCache = [];

    /** @var Collection<int, Filter>|null */
    protected ?Collection $filtersCollection = null;

    /**
     * Define the filters for the table.
     * Override this method in your table class.
     *
     * @return Filter[]
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Get all filters.
     *
     * @return Collection<int, Filter>
     */
    public function getFilters(): Collection
    {
        if (empty($this->filterCache)) {
            $this->filterCache = $this->filters();
            $this->filtersCollection = collect($this->filterCache);
        }

        return $this->filtersCollection;
    }

    /**
     * Apply filters to the query.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function applyFilters(Builder $query): Builder
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
        return ! empty(array_filter($this->filters, fn ($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Get active filter count.
     */
    public function getActiveFilterCount(): int
    {
        return count(array_filter($this->filters, fn ($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Initialize filter default values.
     */
    public function initializeFilters(): void
    {
        foreach ($this->getFilters() as $filter) {
            $field = $filter->getField();
            if (! isset($this->filters[$field]) && $filter->getDefault() !== null) {
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
