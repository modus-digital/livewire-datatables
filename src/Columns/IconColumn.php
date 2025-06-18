<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Closure;
use Illuminate\Support\Str;

class IconColumn extends Column
{
    protected string|Closure $icon = '';

    protected string|Closure|null $countField = null;

    public function icon(string|Closure $svg): self
    {
        $this->icon = $svg;

        return $this;
    }

    public function count(string|Closure $field): self
    {
        $this->countField = $field;

        return $this;
    }

    public function getValue(mixed $record): mixed
    {
        $icon = $this->icon instanceof Closure ? call_user_func($this->icon, $record) : $this->icon;
        $count = null;
        if ($this->countField instanceof Closure) {
            $count = call_user_func($this->countField, $record);
        } elseif ($this->countField !== null) {
            $count = data_get($record, $this->countField);
        }

        if (is_string($icon) && ! Str::contains($icon, '<svg')) {
            $icon = "<i class=\"{$icon}\"></i>";
        }

        if (is_string($icon) && ! Str::contains($icon, '<svg') && Str::contains($icon, 'heroicon')) {
            $icon = svg(name: $icon, class: 'w-4 h-4');
        }

        $countHtml = $count !== null ? "<span class=\"ml-1 text-xs\">{$count}</span>" : '';

        return "<span class=\"inline-flex items-center\">{$icon}{$countHtml}</span>";
    }
}
