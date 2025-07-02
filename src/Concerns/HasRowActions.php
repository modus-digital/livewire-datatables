<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Closure;
use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Actions\RowAction;

trait HasRowActions
{
    /** @var RowAction[] */
    protected array $rowActionsCache = [];

    /**
     * Define row actions for the table.
     * Override this method in your table class.
     *
     * @return RowAction[]
     */
    protected function rowActions(): array
    {
        return [];
    }

    /**
     * Get all row actions.
     *
     * @return Collection<int, RowAction>
     */
    public function getRowActions(): Collection
    {
        if (empty($this->rowActionsCache)) {
            $this->rowActionsCache = $this->rowActions();
        }

        return collect($this->rowActionsCache);
    }

    /**
     * Check if row actions are enabled.
     */
    public function hasRowActions(): bool
    {
        return $this->getRowActions()->isNotEmpty();
    }

    /**
     * Execute a row action.
     */
    public function executeRowAction(string $action, string|int $id): void
    {
        $actions = $this->getRowActions();
        $actionConfig = $actions->first(fn (RowAction $a) => $a->getKey() === $action);

        if (! $actionConfig) {
            return;
        }

        $callback = $actionConfig->getCallback();
        if ($callback instanceof Closure) {
            $record = $this->getModel()->find($id);
            if ($record) {
                $callback($record, $this);
            }
        }
    }

    /**
     * Check if an action is visible for a record.
     */
    public function isActionVisible(RowAction $action, mixed $record): bool
    {
        return $action->isVisible($record);
    }

    /**
     * Get filtered actions for a specific record.
     *
     * @return Collection<int, RowAction>
     */
    public function getRecordActions(mixed $record): Collection
    {
        return $this->getRowActions()->filter(fn (RowAction $action) => $this->isActionVisible($action, $record));
    }
}
