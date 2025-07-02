<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Actions;

use Closure;

class Action
{
    protected string $key;

    protected string $label;

    protected ?string $icon = null;

    protected string $class = '';

    protected ?string $confirmMessage = null;

    protected bool|Closure $visible = true;

    protected ?Closure $callback = null;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public static function make(string $key, string $label): static
    {
        return new static($key, $label);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function confirm(string $message): self
    {
        $this->confirmMessage = $message;

        return $this;
    }

    public function visible(bool|Closure $condition): self
    {
        $this->visible = $condition;

        return $this;
    }

    public function callback(Closure $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getConfirmMessage(): ?string
    {
        return $this->confirmMessage;
    }

    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    public function isVisible(mixed $record = null): bool
    {
        if ($this->visible instanceof Closure) {
            return ($this->visible)($record);
        }

        return $this->visible;
    }
}
