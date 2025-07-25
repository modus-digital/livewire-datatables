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

    /**
     * Get the attribute filtering details.
     * Override in child classes that support attribute filtering.
     *
     * @return array<string, mixed>
     */
    public function getAttributeFilterDetails(): array
    {
        return [];
    }

    /**
     * Check if current filtering requires attribute-based filtering.
     * Override in child classes that support attribute filtering.
     */
    public function requiresAttributeFiltering(): bool
    {
        return false;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    abstract public function apply(Builder $query, mixed $value): Builder;

    abstract public function render(): string;
}
