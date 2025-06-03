<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

trait HasRowSelection
{
    public array $selected = [];
    public bool $selectAll = false;
    public bool $showSelection = true;

    /**
     * Toggle selection for a specific row.
     */
    public function toggleSelection(string|int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_diff($this->selected, [$id]);
        } else {
            $this->selected[] = $id;
        }

        $this->updateSelectAllState();
    }

    /**
     * Toggle all rows selection.
     */
    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectAll = false;
            $this->selected = [];
        } else {
            $this->selectAll = true;
            $this->selected = $this->getVisibleRowIds();
        }
    }

    /**
     * Select all rows on current page.
     */
    public function selectAllOnPage(): void
    {
        $this->selected = array_merge($this->selected, $this->getVisibleRowIds());
        $this->selected = array_unique($this->selected);
        $this->updateSelectAllState();
    }

    /**
     * Deselect all rows.
     */
    public function deselectAll(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    /**
     * Check if a row is selected.
     */
    public function isSelected(string|int $id): bool
    {
        return in_array($id, $this->selected);
    }

    /**
     * Get count of selected rows.
     */
    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    /**
     * Check if any rows are selected.
     */
    public function hasSelected(): bool
    {
        return !empty($this->selected);
    }

    /**
     * Check if selection is enabled.
     */
    public function hasSelection(): bool
    {
        return $this->showSelection;
    }

    /**
     * Disable row selection.
     */
    public function disableSelection(): static
    {
        $this->showSelection = false;
        return $this;
    }

    /**
     * Enable row selection.
     */
    public function enableSelection(): static
    {
        $this->showSelection = true;
        return $this;
    }

    /**
     * Get visible row IDs for current page.
     */
    protected function getVisibleRowIds(): array
    {
        return $this->getRows()->pluck('id')->toArray();
    }

    /**
     * Update select all state based on current selection.
     */
    protected function updateSelectAllState(): void
    {
        $visibleIds = $this->getVisibleRowIds();
        $this->selectAll = !empty($visibleIds) && count(array_intersect($this->selected, $visibleIds)) === count($visibleIds);
    }

    /**
     * Hook for when selection is updated.
     */
    public function updatedSelected(): void
    {
        $this->updateSelectAllState();
    }
}
