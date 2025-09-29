<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Tests;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addNamespace('tests', __DIR__ . '/Feature/views');
    }
}
