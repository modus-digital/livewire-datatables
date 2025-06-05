<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    protected string $name;

    protected string $field;

    protected mixed $default = null;

    protected ?string $placeholder = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->field = strtolower(str_replace(' ', '_', $name));
    }

    final public static function make(string $name): static
    {
        return new static($name);
    }

    public function field(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    abstract public function apply(Builder $query, mixed $value): Builder;

    abstract public function render(): string;
}
