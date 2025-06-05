<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

class IconColumn extends Column
{
    protected string $icon = '';

    protected ?string $countField = null;

    public function icon(string $svg): self
    {
        $this->icon = $svg;

        return $this;
    }

    public function count(string $field): self
    {
        $this->countField = $field;

        return $this;
    }

    public function getValue(mixed $record): mixed
    {
        $icon = $this->icon;
        $count = $this->countField ? data_get($record, $this->countField) : null;
        $countHtml = $count !== null ? "<span class=\"ml-1 text-xs\">{$count}</span>" : '';

        return "<span class=\"inline-flex items-center\">{$icon}{$countHtml}</span>";
    }
}
