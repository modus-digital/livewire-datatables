<?php

namespace ModusDigital\LivewireDatatables\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ModusDigital\LivewireDatatables\LivewireDatatables
 */
class LivewireDatatables extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModusDigital\LivewireDatatables\LivewireDatatables::class;
    }
}
