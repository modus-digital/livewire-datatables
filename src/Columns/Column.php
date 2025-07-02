<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Closure;
use Illuminate\Support\Str;

class Column
{
    protected string $name;

    protected ?string $field = null;

    protected ?string $relationship = null;

    protected ?string $sortField = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected ?Closure $formatCallback = null;

    protected bool $hidden = false;

    protected ?string $width = null;

    protected ?string $align = null;

    protected ?string $view = null;

    public function __construct(string $name)
    {
        $this->name = Str::headline($name);
        $this->field = Str::snake($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function field(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function relationship(string $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function sortField(string $sortField): self
    {
        $this->sortField = $sortField;

        return $this;
    }

    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function format(Closure $callback): self
    {
        $this->formatCallback = $callback;

        return $this;
    }

    public function hidden(bool $hidden = true): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function width(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string
    {
        return $this->field ?? $this->name;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getAlign(): ?string
    {
        return $this->align;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getValue(mixed $record): mixed
    {
        $value = $this->extractValue($record);

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        if ($this->formatCallback) {
            return call_user_func($this->formatCallback, $value, $record);
        }

        return $value;
    }

    protected function extractValue(mixed $record): mixed
    {
        if ($this->relationship) {
            return data_get($record, $this->relationship);
        }

        return data_get($record, $this->getField());
    }

    public function getSortField(): string
    {
        if ($this->sortField) {
            return $this->sortField;
        }

        if ($this->relationship) {
            return $this->relationship;
        }

        return $this->getField();
    }
}
