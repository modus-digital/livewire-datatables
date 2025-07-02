# Livewire Datatables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/modus-digital/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/modus-digital/livewire-datatables)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/modus-digital/livewire-datatables/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/modus-digital/livewire-datatables/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/modus-digital/livewire-datatables/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/modus-digital/livewire-datatables/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/modus-digital/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/modus-digital/livewire-datatables)

A modern, feature-rich **Livewire Datatable** component for the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire). Built with modularity, performance, and developer experience in mind.

## ✨ Features

- 🎨 **Beautiful Tailwind CSS styling** with dark mode support
- 🔍 **Global search** with debounced input and relationship support
- 🗂️ **Advanced filtering** with multiple filter types (Text, Select, Date)
- 📊 **Column sorting** with visual indicators and custom sort fields
- 📄 **Pagination** with customizable page sizes and navigation
- ✅ **Row selection** with bulk actions and "select all" functionality
- 🎯 **Row actions** with customizable buttons and callbacks
- 🔧 **Highly customizable** with trait-based architecture
- 🖼️ **Multiple column types** (Text, Icon, Image) with specialized features
- 🏷️ **Badge support** with dynamic colors and callbacks
- 🔗 **Clickable rows** with custom handlers
- 🔭 **Custom cell views** for complex content rendering
- 📱 **Responsive design** for all screen sizes
- ♿ **Accessibility features** built-in
- 🚀 **Performance optimized** with efficient querying

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| **PHP** | `^8.3` |
| **Laravel** | `^11.0` or `^12.0` |
| **Livewire** | `^3.0` |
| **Tailwind CSS** | `^4.0` |
| **Alpine.js** | `^3.0` |

## 📦 Installation

Install the package via Composer:

```bash
composer require modus-digital/livewire-datatables
```

The package will automatically register its service provider.

### Publishing Views (Optional)

To customize the table appearance, publish the views:

```bash
php artisan vendor:publish --tag="livewire-datatables-views"
```

This publishes all Blade templates to `resources/views/vendor/livewire-datatables/`.

## 🚀 Quick Start

### 1. Generate a Table Component

Use the built-in Artisan command to scaffold a new table:

```bash
php artisan make:table UsersTable --model=App\\Models\\User
```

Or create one manually:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\User;
use ModusDigital\LivewireDatatables\Livewire\Table;
use ModusDigital\LivewireDatatables\Columns\Column;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;

class UsersTable extends Table
{
    protected string $model = User::class;

    protected function columns(): array
    {
        return [
            Column::make('Name')
                ->field('name')
                ->sortable()
                ->searchable(),

            Column::make('Email')
                ->field('email')
                ->sortable()
                ->searchable(),

            TextColumn::make('Status')
                ->field('status')
                ->badge()
                ->sortable(),

            Column::make('Created')
                ->field('created_at')
                ->sortable()
                ->format(fn($value) => $value->diffForHumans()),
        ];
    }

    protected function filters(): array
    {
        return [
            SelectFilter::make('Status')->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
                'banned' => 'Banned',
            ]),
        ];
    }
}
```

### 2. Use in Your Blade Template

```blade
<div>
    <livewire:users-table />
</div>
```

## 📚 Documentation

### Column Types

#### Base Column

The foundation for all column types with essential features:

```php
Column::make('Name')
    ->field('name')                    // Database field
    ->sortable()                       // Enable sorting
    ->searchable()                     // Include in global search
    ->hidden()                         // Hide column
    ->width('w-32')                    // Set width classes
    ->align('center')                  // Alignment: left, center, right
    ->view('custom.cell')              // Custom view
    ->relationship('profile.role')     // Access relationship data
    ->sortField('custom_sort_field')   // Custom sort field
    ->format(fn($value, $record) => strtoupper($value)); // Format callback
```

#### TextColumn

Specialized for text content with additional features:

```php
TextColumn::make('Description')
    ->field('description')
    ->limit(50)                        // Truncate text
    ->badge()                          // Render as badge
    ->badge('blue')                    // Badge with specific color
    ->badge(fn($record) => $record->priority_color) // Dynamic badge color
    ->fullWidth();                     // Badge spans full cell width
```

#### IconColumn

Display icons with optional counts:

```php
IconColumn::make('Status')
    ->field('is_active')
    ->icon(fn($record) => $record->is_active ? 'fa-check' : 'fa-times')
    ->icon('<svg>...</svg>')           // Static SVG icon
    ->count(fn($record) => $record->notifications_count); // Show count badge
```

#### ImageColumn

Display images with fallback support:

```php
ImageColumn::make('Avatar')
    ->field('avatar_url')
    ->src(fn($record) => $record->getAvatarUrl()) // Dynamic source
    ->fallback('/images/default-avatar.png')      // Fallback image
    ->rounded()                                   // Apply rounded styling
    ->size('w-10 h-10');                         // Size classes
```

### Filters

#### TextFilter

Search within specific fields:

```php
TextFilter::make('Name')
    ->field('name')
    ->placeholder('Search names...')
    ->operator('like');                // Operators: like, =, !=, >, <, >=, <=
```

#### SelectFilter

Dropdown selection with predefined options:

```php
SelectFilter::make('Status')
    ->field('status')
    ->options([
        'active' => 'Active Users',
        'inactive' => 'Inactive Users',
        'banned' => 'Banned Users',
    ])
    ->placeholder('All Statuses')
    ->multiple();                      // Allow multiple selections
```

#### DateFilter

Date range filtering:

```php
DateFilter::make('Created')
    ->field('created_at')
    ->placeholder('Select date range...')
    ->format('Y-m-d');                 // Date format
```

### Row Selection & Bulk Actions

Enable row selection and define bulk actions:

```php
class UsersTable extends Table
{
    public bool $showSelection = true; // Enable row selection

    protected function bulkActions(): array
    {
        return [
            [
                'name' => 'Delete Selected',
                'key' => 'delete',
                'class' => 'bg-red-600 hover:bg-red-700 text-white',
            ],
            [
                'name' => 'Export Selected',
                'key' => 'export',
                'class' => 'bg-green-600 hover:bg-green-700 text-white',
            ],
        ];
    }

    public function bulkActionDelete($rows)
    {
        $rows->each->delete();
        session()->flash('message', 'Selected users deleted successfully.');
    }

    public function bulkActionExport($rows)
    {
        // Export logic here
        return response()->download($this->generateExport($rows));
    }
}
```

### Row Actions

Add action buttons to each row:

```php
use ModusDigital\LivewireDatatables\Actions\RowAction;

protected function rowActions(): array
{
    return [
        RowAction::make('edit', 'Edit')
            ->icon('<svg>...</svg>')
            ->class('text-blue-600 hover:text-blue-900'),

        RowAction::make('delete', 'Delete')
            ->icon('<svg>...</svg>')
            ->class('text-red-600 hover:text-red-900'),
    ];
}

public function rowActionEdit($row)
{
    return redirect()->route('users.edit', $row);
}

public function rowActionDelete($row)
{
    $row->delete();
    session()->flash('message', 'User deleted successfully.');
}
```

### Global Actions

Add header-level actions:

```php
use ModusDigital\LivewireDatatables\Actions\Action;

protected function actions(): array
{
    return [
        Action::make('create', 'Add User')
            ->class('bg-blue-600 hover:bg-blue-700 text-white')
            ->label('+ Add User'),
    ];
}

public function globalAction($action)
{
    if ($action === 'create') {
        return redirect()->route('users.create');
    }
}
```

### Clickable Rows

Make entire rows clickable:

```php
class UsersTable extends Table
{
    public function showRecord(string|int $id): void
    {
        // Redirect to detail page
        return redirect()->route('users.show', $id);

        // Or dispatch Livewire event
        // $this->dispatch('openUserDrawer', id: $id);
    }
}
```

### Pagination Configuration

Customize pagination behavior:

```php
class UsersTable extends Table
{
    public int $perPage = 25;                    // Default page size
    public array $perPageOptions = [10, 25, 50, 100]; // Available options
    public bool $showPerPageSelector = true;     // Show page size selector
}
```

### Search Configuration

Customize search behavior:

```php
class UsersTable extends Table
{
    protected bool $searchable = true;           // Enable global search
    protected string $searchPlaceholder = 'Search users...'; // Custom placeholder
}
```

### Empty State Customization

Customize the empty state message:

```php
class UsersTable extends Table
{
    public string $emptyStateTitle = 'No users found';
    public string $emptyStateDescription = 'Get started by creating your first user.';
}
```

### Custom Query Building

Override the base query for complex scenarios:

```php
protected function query(): Builder
{
    return $this->getModel()
        ->query()
        ->with(['profile', 'roles'])
        ->where('tenant_id', auth()->user()->tenant_id);
}
```

### Relationship Handling

Access relationship data in columns:

```php
Column::make('Role')
    ->relationship('profile.role')     // Nested relationship
    ->searchable()                     // Will search in relationship
    ->sortable(),                      // Will sort by relationship field

Column::make('Department')
    ->field('department_id')
    ->relationship('department.name')
    ->sortField('departments.name'),   // Custom sort field for relationship
```

## 🎨 Styling & Customization

### Dark Mode Support

The package includes full dark mode support using Tailwind's `dark:` variants. Ensure your project has dark mode configured:

```css
@source '../../vendor/modus-digital/livewire-datatables/resources/views/**/*.blade.php';
```

### Custom Views

Create custom cell views for complex content:

```php
Column::make('Actions')
    ->view('components.user-actions')
    ->width('w-32'),
```

```blade
<!-- resources/views/components/user-actions.blade.php -->
<div class="flex space-x-2">
    <button wire:click="editUser({{ $record->id }})" class="text-blue-600">
        Edit
    </button>
    <button wire:click="deleteUser({{ $record->id }})" class="text-red-600">
        Delete
    </button>
</div>
```

### Badge Colors

Available badge colors for TextColumn:

- `gray` (default)
- `red`
- `yellow`
- `green`
- `blue`
- `indigo`
- `purple`
- `pink`

```php
TextColumn::make('Status')
    ->badge(fn($record) => match($record->status) {
        'active' => 'green',
        'pending' => 'yellow',
        'banned' => 'red',
        default => 'gray'
    });
```

## 🏗️ Architecture

The package follows a modular trait-based architecture:

### Core Traits

- **`HasColumns`** - Column management and rendering (120 lines)
- **`HasFilters`** - Filter functionality and application (149 lines)
- **`HasPagination`** - Pagination configuration (67 lines)
- **`HasSorting`** - Sorting logic and state management (132 lines)
- **`HasRowSelection`** - Row selection and bulk actions (142 lines)
- **`HasRowActions`** - Individual row action handling (92 lines)
- **`HasActions`** - Global header actions (59 lines)

Each trait is focused, testable, and can be understood independently.

### Directory Structure

```
src/
├── Actions/           # Action classes for global and row actions
├── Columns/           # Column type classes with specialized features
├── Commands/          # Artisan command for generating tables
├── Concerns/          # Traits for modular functionality
├── Filters/           # Filter classes for different data types
├── Livewire/          # Main Table component
└── LivewireDatatablesServiceProvider.php

resources/
├── stubs/             # Template for make:table command
└── views/
    ├── partials/      # Reusable view components
    └── table.blade.php # Main table view

tests/
├── Feature/           # Integration tests
├── Unit/              # Unit tests for each component
└── Fixtures/          # Test data and models
```

## 🧪 Testing

The package includes comprehensive tests using **Pest 3**:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Run static analysis
composer analyse

# Fix code style
composer format
```

### Test Coverage

- ✅ All traits individually tested
- ✅ Column types and their features
- ✅ Filter functionality and operators
- ✅ Sorting mechanisms and edge cases
- ✅ Pagination behavior
- ✅ Row selection and bulk actions
- ✅ Search functionality including relationships
- ✅ Architecture rules with Pest Arch plugin

## 🔧 Development

### Code Quality Tools

The package uses several tools to maintain high code quality:

- **Pest 3** - Modern PHP testing framework
- **Larastan** - Static analysis for Laravel
- **Laravel Pint** - Code style fixer
- **PHPStan** - Static analysis with strict rules

### Contributing Workflow

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass: `composer test`
5. Fix code style: `composer format`
6. Run static analysis: `composer analyse`
7. Submit a pull request

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes and version history.


## 👥 Credits

- [Alex van Steenhoven](https://github.com/AlexVanSteenhoven) - Creator & Maintainer
- [Modus Digital](https://github.com/modus-digital) - Organization
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
