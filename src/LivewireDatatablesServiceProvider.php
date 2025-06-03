<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables;

use ModusDigital\LivewireDatatables\Commands\LivewireDatatablesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivewireDatatablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('livewire-datatables')
            ->hasViews()
            ->hasCommand(LivewireDatatablesCommand::class);
    }

    public function packageBooted(): void
    {
        // Load the views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-datatables');

        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-datatables'),
            ], 'livewire-datatables-views');
        }
    }
}
