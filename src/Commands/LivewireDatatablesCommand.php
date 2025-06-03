<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class LivewireDatatablesCommand extends Command
{
    /** @var string */
    protected $signature = 'make:table
        {name? : The table class to generate (e.g. UsersTable or Dashboard/UsersTable)}
        {model? : The model class to use (e.g. App\Models\User)}';

    /** @var string */
    protected $description = 'Generate a new Livewire table component class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name') ?: $this->ask('Table class name (e.g. UsersTable)');
        $model = $this->argument('model') ?: $this->ask('Model class name (e.g. App\\Models\\User)');

        if (empty($name)) {
            $this->error('Table class name is required');

            return self::FAILURE;
        }

        if (! $this->createTableFile($files, $name, $model)) {
            return self::FAILURE;
        }

        $this->info("Table class {$name} created successfully.");

        return self::SUCCESS;
    }

    /**
     * Build the proper path/namespace, ensure directories exist, and write the file.
     */
    private function createTableFile(Filesystem $files, string $rawName, string $model): bool
    {
        $segments = preg_split('/[\/\\\\]+/', $rawName);

        $class = Str::studly(array_pop($segments));
        $subPath = implode(DIRECTORY_SEPARATOR, $segments);
        $basePath = app_path('Livewire/Tables');
        $directory = $subPath ? $basePath . DIRECTORY_SEPARATOR . $subPath : $basePath;
        $path = $directory . DIRECTORY_SEPARATOR . "{$class}.php";

        if ($files->exists($path)) {
            $this->error("{$class} already exists at {$path}");

            return false;
        }

        $namespaceSuffix = $segments ? '\\' . implode('\\', array_map([Str::class, 'studly'], $segments)) : '';
        $namespace = 'App\\Livewire\\Tables' . $namespaceSuffix;

        $stub = $files->get(__DIR__ . '/../../resources/stubs/component.stub');

        $replaced = str_replace(
            ['{{ $namespace }}', '{{ $name }}', '{{ $model }}'],
            [$namespace, $class, $model],
            $stub
        );

        $files->ensureDirectoryExists($directory);
        $files->put($path, $replaced);

        return true;
    }
}
