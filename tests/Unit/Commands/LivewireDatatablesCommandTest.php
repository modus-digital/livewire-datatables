<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Illuminate\Filesystem\Filesystem;
use ModusDigital\LivewireDatatables\Commands\LivewireDatatablesCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    $this->filesystem = new Filesystem;
    $this->command = new LivewireDatatablesCommand;

    // Set up proper console input/output for the command
    $bufferedOutput = new BufferedOutput;
    $input = new ArrayInput([]);
    $outputStyle = new OutputStyle($input, $bufferedOutput);
    $this->command->setOutput($outputStyle);
    $this->command->setInput($input);

    // Use the actual app path from testbench, but clean it up first
    $appPath = app_path('Livewire/Tables');
    if ($this->filesystem->exists($appPath)) {
        $this->filesystem->deleteDirectory($appPath);
    }
});

afterEach(function () {
    // Clean up the generated files
    $appPath = app_path('Livewire/Tables');
    if ($this->filesystem->exists($appPath)) {
        $this->filesystem->deleteDirectory($appPath);
    }
});

describe('Command Basic Properties', function () {
    it('has correct command signature', function () {
        expect($this->command->getName())->toBe('make:table');
    });

    it('has correct command description', function () {
        expect($this->command->getDescription())->toBe('Generate a new Livewire table component class');
    });

    it('can be instantiated', function () {
        expect($this->command)->toBeInstanceOf(LivewireDatatablesCommand::class);
    });
});

describe('Private Method Testing', function () {
    it('handles createTableFile method correctly', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'TestTable', 'App\\Models\\Test');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/TestTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();
    });

    it('returns false when createTableFile fails for existing file', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));
        $existingFile = app_path('Livewire/Tables/TestTable.php');
        $this->filesystem->put($existingFile, '<?php // existing');

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'TestTable', 'App\\Models\\Test');

        expect($result)->toBeFalse();
    });

    it('returns false for invalid table name', function () {
        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, '', 'App\\Models\\Test');

        expect($result)->toBeFalse();
    });
});

describe('File Content Generation', function () {
    it('generates correct namespace for simple table', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $method->invoke($this->command, $this->filesystem, 'UsersTable', 'App\\Models\\User');

        $content = $this->filesystem->get(app_path('Livewire/Tables/UsersTable.php'));

        expect($content)->toContain('namespace App\\Livewire\\Tables;');
        expect($content)->toContain('class UsersTable extends Table');
        expect($content)->toContain('protected string $model = App\\Models\\User::class;');
    });

    it('generates correct namespace for nested table', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $method->invoke($this->command, $this->filesystem, 'Admin/UsersTable', 'App\\Models\\User');

        $content = $this->filesystem->get(app_path('Livewire/Tables/Admin/UsersTable.php'));

        expect($content)->toContain('namespace App\\Livewire\\Tables\\Admin;');
        expect($content)->toContain('class UsersTable extends Table');
        expect($content)->toContain('protected string $model = App\\Models\\User::class;');
    });

    it('includes all required imports and methods', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $method->invoke($this->command, $this->filesystem, 'UsersTable', 'App\\Models\\User');

        $content = $this->filesystem->get(app_path('Livewire/Tables/UsersTable.php'));

        expect($content)->toContain('use ModusDigital\\LivewireDatatables\\Livewire\\Table;');
        expect($content)->toContain('use ModusDigital\\LivewireDatatables\\Columns\\Column;');
        expect($content)->toContain('protected function columns(): array');
        expect($content)->toContain('protected function filters(): array');
        expect($content)->toContain('protected function actions(): array');
        expect($content)->toContain('protected function rowActions(): array');
    });
});

describe('Directory Management', function () {
    it('creates directories when they do not exist', function () {
        // Don't create the directory beforehand

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'Admin/Reports/UsersTable', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedDir = app_path('Livewire/Tables/Admin/Reports');
        expect($this->filesystem->isDirectory($expectedDir))->toBeTrue();

        $expectedFile = $expectedDir . '/UsersTable.php';
        expect($this->filesystem->exists($expectedFile))->toBeTrue();
    });
});

describe('Edge Cases and Input Validation', function () {
    it('handles studly case conversion correctly', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'user_table', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/UserTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();

        $content = $this->filesystem->get($expectedPath);
        expect($content)->toContain('class UserTable extends Table');
    });

    it('handles mixed case and special characters in subdirectories', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'admin_panel/user_management/users_table', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/admin_panel/user_management/UsersTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();

        $content = $this->filesystem->get($expectedPath);
        expect($content)->toContain('namespace App\\Livewire\\Tables\\AdminPanel\\UserManagement;');
        expect($content)->toContain('class UsersTable extends Table');
    });

    it('handles backslash separators in name', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'Admin\\UsersTable', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/Admin/UsersTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();
    });

    it('creates table file with subdirectory', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'Admin/UsersTable', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/Admin/UsersTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();
    });

    it('creates table file with nested subdirectories', function () {
        $this->filesystem->ensureDirectoryExists(app_path('Livewire/Tables'));

        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('createTableFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $this->filesystem, 'Admin/Reports/UsersTable', 'App\\Models\\User');

        expect($result)->toBeTrue();

        $expectedPath = app_path('Livewire/Tables/Admin/Reports/UsersTable.php');
        expect($this->filesystem->exists($expectedPath))->toBeTrue();
    });
});
