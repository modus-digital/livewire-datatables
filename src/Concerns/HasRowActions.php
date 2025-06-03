<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Closure;
use Illuminate\Support\Collection;

trait HasRowActions
{
    /** @var array<string, array> */
    protected array $rowActionsCache = [];

    /**
     * Define row actions for the table.
     * Override this method in your table class.
     */
    protected function rowActions(): array
    {
        return [];
    }

    /**
     * Get all row actions.
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
        $actionConfig = $actions->firstWhere('key', $action);

        if (!$actionConfig) {
            return;
        }

        $callback = $actionConfig['callback'] ?? null;
        if ($callback instanceof Closure) {
            $record = $this->getModel()->find($id);
            if ($record) {
                $callback($record, $this);
            }
        }
    }

    /**
     * Create a row action configuration.
     */
    protected function action(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'callback' => null,
            'icon' => null,
            'class' => 'text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white',
            'confirmMessage' => null,
            'visible' => true,
        ];
    }

    /**
     * Add callback to action.
     */
    protected function actionCallback(array $action, Closure $callback): array
    {
        $action['callback'] = $callback;
        return $action;
    }

    /**
     * Add icon to action.
     */
    protected function actionIcon(array $action, string $icon): array
    {
        $action['icon'] = $icon;
        return $action;
    }

    /**
     * Add CSS class to action.
     */
    protected function actionClass(array $action, string $class): array
    {
        $action['class'] = $class;
        return $action;
    }

    /**
     * Add confirmation message to action.
     */
    protected function actionConfirm(array $action, string $message): array
    {
        $action['confirmMessage'] = $message;
        return $action;
    }

    /**
     * Set action visibility condition.
     */
    protected function actionVisible(array $action, bool|Closure $condition): array
    {
        $action['visible'] = $condition;
        return $action;
    }

    /**
     * Check if an action is visible for a record.
     */
    public function isActionVisible(array $action, mixed $record): bool
    {
        $visible = $action['visible'] ?? true;

        if ($visible instanceof Closure) {
            return $visible($record);
        }

        return (bool) $visible;
    }

    /**
     * Get filtered actions for a specific record.
     */
    public function getRecordActions(mixed $record): Collection
    {
        return $this->getRowActions()->filter(fn($action) => $this->isActionVisible($action, $record));
    }
}
