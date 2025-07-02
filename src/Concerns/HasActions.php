<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Actions\Action;

trait HasActions
{
    /** @var Action[] */
    protected array $actionsCache = [];

    /**
     * @return Action[]
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * @return Collection<int, Action>
     */
    public function getActions(): Collection
    {
        if (empty($this->actionsCache)) {
            $this->actionsCache = $this->actions();
        }

        return collect($this->actionsCache);
    }

    public function hasActions(): bool
    {
        return $this->getActions()->isNotEmpty();
    }

    public function executeAction(string $key): void
    {
        $action = $this->getActions()->first(fn (Action $a) => $a->getKey() === $key);

        if (! $action) {
            return;
        }

        $callback = $action->getCallback();

        if ($callback) {
            $callback($this);
        }
    }
}
