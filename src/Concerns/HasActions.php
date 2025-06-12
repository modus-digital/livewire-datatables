<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Actions\Action;

trait HasActions
{
    /** @var Action[] */
    protected array $actionsCache = [];

    protected function actions(): array
    {
        return [];
    }

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
        $callback = $action?->getCallback();

        if ($callback) {
            $callback($this);
        }
    }
}
