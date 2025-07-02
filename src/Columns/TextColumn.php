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

    protected bool $fullWidth = false;

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

        $this->fullWidth = true;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function fullWidth(bool $fullWidth = true): self
    {
        $this->fullWidth = $fullWidth;

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
            $classes = 'px-4 py-1 tracking-wider inline-flex text-xs leading-5 font-semibold rounded-sm';
            $classes .= $this->fullWidth ? ' w-full justify-center' : ' ';

            // Use a switch statement to handle different color variants
            // This ensures all classes are statically available for JIT mode
            switch ($color) {
                case 'gray':
                    // Use zinc for better dark mode consistency
                    $classes .= ' bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200';
                    break;
                case 'red':
                    $classes .= ' bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                    break;
                case 'yellow':
                    $classes .= ' bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                    break;
                case 'green':
                    $classes .= ' bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                    break;
                case 'blue':
                    $classes .= ' bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                    break;
                case 'indigo':
                    $classes .= ' bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
                    break;
                case 'purple':
                    $classes .= ' bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
                    break;
                case 'pink':
                    $classes .= ' bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200';
                    break;
                case 'orange':
                    $classes .= ' bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
                    break;
                case 'teal':
                    $classes .= ' bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200';
                    break;
                case 'cyan':
                    $classes .= ' bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200';
                    break;
                case 'lime':
                    $classes .= ' bg-lime-100 text-lime-800 dark:bg-lime-900 dark:text-lime-200';
                    break;
                case 'emerald':
                    $classes .= ' bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
                    break;
                case 'sky':
                    $classes .= ' bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200';
                    break;
                case 'violet':
                    $classes .= ' bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-200';
                    break;
                case 'fuchsia':
                    $classes .= ' bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-900 dark:text-fuchsia-200';
                    break;
                case 'rose':
                    $classes .= ' bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200';
                    break;
                case 'amber':
                    $classes .= ' bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
                    break;
                default:
                    // Default to zinc for consistency
                    $classes .= ' bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200';
                    break;
            }

            return "<span class=\"{$classes}\">{$value}</span>";
        }

        return $value;
    }
}
