<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Closure;

class ImageColumn extends Column
{
    protected bool $circle = false;

    protected string|Closure|null $src = null;

    public function circle(bool $circle = true): self
    {
        $this->circle = $circle;

        return $this;
    }

    public function src(string|Closure $src): self
    {
        $this->src = $src;

        return $this;
    }

    public function getValue(mixed $record): mixed
    {
        if ($this->src instanceof Closure) {
            $src = call_user_func($this->src, $record);
        } elseif ($this->src !== null) {
            $src = $this->src;
        } else {
            $src = parent::getValue($record);
        }
        $class = $this->circle ? 'rounded-full' : 'rounded-md';

        return "<img src=\"{$src}\" class=\"h-8 w-8 object-cover {$class}\" />";
    }
}
