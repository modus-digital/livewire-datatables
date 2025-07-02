<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Columns\Column;

trait HasColumns
{
    /** @var Column[] */
    protected array $columnCache = [];

    /**
     * Define the columns for the table.
     * Override this method in your table class.
     *
     * @return Column[]
     */
    protected function columns(): array
    {
        return [];
    }

    /**
     * Get all columns.
     *
     * @return Collection<int, Column>
     */
    public function getColumns(): Collection
    {
        if (empty($this->columnCache)) {
            $this->columnCache = $this->columns();
        }

        return collect($this->columnCache)->filter(fn(Column $column) => ! $column->isHidden());
    }

    /**
     * Get searchable columns.
     *
     * @return Collection<int, Column>
     */
    public function getSearchableColumns(): Collection
    {
        return $this->getColumns()->filter(fn(Column $column) => $column->isSearchable());
    }

    /**
     * Get sortable columns.
     *
     * @return Collection<int, Column>
     */
    public function getSortableColumns(): Collection
    {
        return $this->getColumns()->filter(fn(Column $column) => $column->isSortable());
    }

    /**
     * Get a specific column by field name.
     */
    public function getColumn(string $field): ?Column
    {
        return $this->getColumns()->first(fn(Column $column) => $column->getField() === $field);
    }

    /**
     * Check if a column is sortable.
     */
    public function isColumnSortable(string $field): bool
    {
        $column = $this->getColumn($field);

        return $column && $column->isSortable();
    }

    /**
     * Get the sort field for a column (handles relationships).
     */
    public function getColumnSortField(string $field): string
    {
        $column = $this->getColumn($field);

        return $column ? $column->getSortField() : $field;
    }

    /**
     * Render cell value for a column.
     */
    public function renderCell(Column $column, mixed $record): mixed
    {
        $value = $column->getValue($record);

        /** @var view-string|null $view */
        $view = $column->getView();

        if ($view) {
            return view($view, [
                'record' => $record,
                'value' => $value,
            ])->render();
        }

        return $value;
    }
}
