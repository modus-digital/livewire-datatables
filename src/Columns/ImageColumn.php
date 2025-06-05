<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

class ImageColumn extends Column
{
    protected bool $circle = false;

    public function circle(bool $circle = true): self
    {
        $this->circle = $circle;

        return $this;
    }

    public function getValue(mixed $record): mixed
    {
        $src = parent::getValue($record);
        $class = $this->circle ? 'rounded-full' : 'rounded-md';

        return "<img src=\"{$src}\" class=\"h-8 w-8 object-cover {$class}\" />";
    }
}
