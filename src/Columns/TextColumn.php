<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Closure;
use Illuminate\Support\Str;

class TextColumn extends Column
{
    protected bool $badge = false;

    protected string $badgeColor = 'gray';

    protected ?Closure $badgeCallback = null;

    protected ?int $limit = null;

    public function badge(bool|string|Closure $badge = true, string $color = 'gray'): self
    {
        if ($badge instanceof Closure) {
            $this->badgeCallback = $badge;
            $this->badgeColor = $color;
            $this->badge = true;
        } elseif (is_string($badge)) {
            $this->badge = true;
            $this->badgeColor = $badge;
        } else {
            $this->badge = $badge;
            $this->badgeColor = $color;
        }

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

        $badge = $this->badge;
        $color = $this->badgeColor;

        if ($this->badgeCallback) {
            $result = call_user_func($this->badgeCallback, $record);
            if (is_string($result)) {
                $color = $result;
                $badge = true;
            } else {
                $badge = (bool) $result;
            }
        }

        if ($badge) {
            $classes = "px-2 inline-flex text-xs leading-5 font-semibold rounded-md bg-{$color}-100 text-{$color}-800 dark:bg-{$color}-800 dark:text-{$color}-100";

            return "<span class=\"{$classes}\">{$value}</span>";
        }

        return $value;
    }
}
