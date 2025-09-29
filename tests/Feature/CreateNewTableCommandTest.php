<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    /** @var Filesystem $fs */
    $fs = app(Filesystem::class);
    $fs->deleteDirectory(app_path('Livewire'));
});

it('generates a table when name is provided and uses the default model value explicitly', function (): void {
    $this->artisan('make:table', [
        'name' => 'UsersTable',
        '--model' => '\\App\\Models\\User',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'UsersTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Tables;')
        ->toContain('class UsersTable extends Table')
        ->toContain('protected string $model = \\App\\Models\\User::class;');
});

it('accepts a custom model via --model option (without leading backslash)', function (): void {
    $this->artisan('make:table', [
        'name' => 'PostsTable',
        '--model' => 'App\\Models\\Post',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'PostsTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Tables;')
        ->toContain('class PostsTable extends Table')
        ->toContain('protected string $model = App\\Models\\Post::class;');
});

it('accepts a custom model via --model option (with leading backslash)', function (): void {
    $this->artisan('make:table', [
        'name' => 'InvoicesTable',
        '--model' => '\\App\\Models\\Invoice',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'InvoicesTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Tables;')
        ->toContain('class InvoicesTable extends Table')
        ->toContain('protected string $model = \\App\\Models\\Invoice::class;');
});

it('creates nested directories and proper namespace with forward slashes', function (): void {
    $this->artisan('make:table', [
        'name' => 'Admin/Reports/SalesTable',
        '--model' => 'App\\Models\\Sale',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'Reports' . DIRECTORY_SEPARATOR . 'SalesTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Tables\\Admin\\Reports;')
        ->toContain('class SalesTable extends Table')
        ->toContain('protected string $model = App\\Models\\Sale::class;');
});

it('creates nested directories when using Windows-style backslashes in the name', function (): void {
    $this->artisan('make:table', [
        'name' => 'Backoffice\\UsersTable',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'Backoffice' . DIRECTORY_SEPARATOR . 'UsersTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Tables\\Backoffice;')
        ->toContain('class UsersTable extends Table');
});

it('does not overwrite existing files and returns a failure code', function (): void {
    $name = 'ProductsTable';

    $this->artisan('make:table', [
        'name' => $name,
        '--model' => 'App\\Models\\Product',
    ])->assertExitCode(0);

    $path = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'ProductsTable.php');
    $before = filemtime($path) ?: time();

    // Second run should fail and keep file unchanged
    $this->artisan('make:table', [
        'name' => $name,
        '--model' => 'App\\Models\\Product',
    ])->assertExitCode(1);

    $after = filemtime($path) ?: time();
    expect($after)->toBe($before);
});

it('fails with an invalid whitespace-only name and does not create a file', function (): void {
    $this->artisan('make:table', [
        'name' => '   ',
    ])->assertExitCode(1);

    $expectedDir = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables');
    expect(is_dir($expectedDir))->toBeFalse();
});

it('does not duplicate the Tables segment in the namespace when provided in the name', function (): void {
    $this->artisan('make:table', [
        'name' => 'Admin/Tables/OrdersTable',
        '--model' => 'App\\Models\\Order',
    ])->assertExitCode(0);

    $expectedPath = app_path('Livewire' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'Tables' . DIRECTORY_SEPARATOR . 'OrdersTable.php');

    expect(is_file($expectedPath))->toBeTrue();

    $contents = file_get_contents($expectedPath);
    expect($contents)
        ->toContain('namespace App\\Livewire\\Admin\\Tables;')
        ->toContain('class OrdersTable extends Table')
        ->toContain('protected string $model = App\\Models\\Order::class;');
});
