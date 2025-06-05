<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Illuminate\Support\Str;

class TextColumn extends Column
{
    protected bool $badge = false;

    protected string $badgeColor = 'gray';

    protected ?int $limit = null;

    public function badge(bool $badge = true, string $color = 'gray'): self
    {
        $this->badge = $badge;
        $this->badgeColor = $color;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getValue(mixed $record): mixed
    {
        $value = parent::getValue($record);

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        if (is_string($value) && $this->limit) {
            $value = Str::limit($value, $this->limit);
        }

        if ($this->badge) {
            $classes = "px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{$this->badgeColor}-100 text-{$this->badgeColor}-800 dark:bg-{$this->badgeColor}-800 dark:text-{$this->badgeColor}-100";

            return "<span class=\"{$classes}\">{$value}</span>";
        }

        return $value;
    }
}
