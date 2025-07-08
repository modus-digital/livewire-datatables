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
            $value = $this->getFilterValue($filter->getField());

            if ($value !== null && $value !== '' && $value !== []) {
                $query = $filter->apply($query, $value);
            }
        }

        return $query;
    }

    /**
     * Get the filter value for a given field.
     */
    protected function getFilterValue(string $field): mixed
    {
        // Handle dotted field names (e.g., 'client.status') by using data_get
        // which can access nested array values like $filters['client']['status']
        return data_get($this->filters, $field);
    }

    /**
     * Check if any filter requires attribute-based filtering.
     */
    public function requiresAttributeFiltering(): bool
    {
        foreach ($this->getFilters() as $filter) {
            $value = $this->getFilterValue($filter->getField());

            if ($value !== null && $value !== '' && $value !== []) {
                // Apply the filter to check if it requires attribute filtering
                $dummyQuery = $this->getModel()->newQuery();
                $filter->apply($dummyQuery, $value);

                if (method_exists($filter, 'requiresAttributeFiltering') && $filter->requiresAttributeFiltering()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all active attribute filters.
     */
    public function getActiveAttributeFilters(): array
    {
        $attributeFilters = [];

        foreach ($this->getFilters() as $filter) {
            $value = $this->getFilterValue($filter->getField());

            if ($value !== null && $value !== '' && $value !== []) {
                // Apply the filter to check if it requires attribute filtering
                $dummyQuery = $this->getModel()->newQuery();
                $filter->apply($dummyQuery, $value);

                if (method_exists($filter, 'requiresAttributeFiltering') && $filter->requiresAttributeFiltering()) {
                    $details = $filter->getAttributeFilterDetails();
                    $details['filter_instance'] = $filter;
                    $attributeFilters[] = $details;
                }
            }
        }

        return $attributeFilters;
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
        // Handle dotted field names by using data_forget for nested arrays
        data_forget($this->filters, $field);
        $this->resetPage();
    }

    /**
     * Check if any filters are active.
     */
    public function hasActiveFilters(): bool
    {
        return ! empty(array_filter($this->getFilterValues(), fn ($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Get active filter count.
     */
    public function getActiveFilterCount(): int
    {
        return count(array_filter($this->getFilterValues(), fn ($value) => $value !== null && $value !== '' && $value !== []));
    }

    /**
     * Get all filter values, handling dotted field names.
     */
    protected function getFilterValues(): array
    {
        $values = [];
        foreach ($this->getFilters() as $filter) {
            $field = $filter->getField();
            $value = data_get($this->filters, $field);
            if ($value !== null) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * Initialize filter default values.
     */
    public function initializeFilters(): void
    {
        foreach ($this->getFilters() as $filter) {
            $field = $filter->getField();
            $currentValue = data_get($this->filters, $field);

            if ($currentValue === null && $filter->getDefault() !== null) {
                // Use data_set to handle dotted field names for nested arrays
                data_set($this->filters, $field, $filter->getDefault());
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
