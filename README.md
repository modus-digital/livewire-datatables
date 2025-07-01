# Livewire Datatables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/modus-digital/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/modus-digital/livewire-datatables)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/modus-digital/livewire-datatables/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/modus-digital/livewire-datatables/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/modus-digital/livewire-datatables/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/modus-digital/livewire-datatables/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/modus-digital/livewire-datatables.svg?style=flat-square)](https://packagist.org/packages/modus-digital/livewire-datatables)

A reusable, highly-customizable **Livewire Datatable** component for the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire). Built with modularity, testability, and developer experience in mind.

## Features

- 🎨 **Beautiful Tailwind CSS styling** with dark mode support
- 🔍 **Global search** with debounced input (300ms)
- 🗂️ **Advanced filtering** with multiple filter types
- 📊 **Column sorting** with visual indicators
- 📄 **Pagination** with customizable page sizes
- ✅ **Row selection** with bulk actions
- 🔧 **Highly customizable** with traits and concerns
- 🔭 **Custom cell views** for rendering complex content
- 🧪 **Fully tested** with Pest 3
- 📱 **Responsive design** for all screen sizes
- ♿ **Accessibility features** built-in

## Package Overview

This repository is a Laravel package that ships a ready-to-use datatable component built with Livewire. The goal is to provide a clean starting point that you can easily extend.

**Key directories**

- `src/` – The `Table` Livewire component, traits, column definitions and filters.
- `resources/views/` – Blade templates that render the table.
- `resources/stubs/` – Stub used by the `make:table` command.
- `tests/` – Pest tests and architecture rules.

The `Table` class orchestrates querying your model, applying search, filters, sorting and pagination so your table class focuses on describing columns and filters.

## Installation

You can install the package via composer:

```bash
composer require modus-digital/livewire-datatables
```

Optionally, you can publish the views using:

```bash
php artisan vendor:publish --tag="livewire-datatables-views"
```

## Quick Start

### 1. Create a Table Component

```php
<?php

use App\Models\User;
use ModusDigital\LivewireDatatables\Table;
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

            Column::make('Role')
                ->relationship('profile', 'role')
                ->sortable(),

            TextColumn::make('Status')
                ->field('status')
                ->badge() // badges span the full cell width by default
                ->limit(10),
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

## How It Works

The table component builds a query from your model, applies global search, filters and sorting, then paginates the results. Each column can be marked sortable or searchable and may format its value using a callback. Filters implement a simple `apply()` method so you can easily add custom logic. The included Blade views render everything with Tailwind classes.

## Advanced Usage

### Column Configuration

To render complex HTML or even embed a Livewire component, provide a custom view using `->view()`. The view receives the row `record` and the column `value`.

```php
Column::make('Avatar')
    ->field('avatar_url')
    ->view('components.avatar') // Custom view
    ->attributes(['header_class' => 'w-16']),

Column::make('Created')
    ->field('created_at')
    ->sortable()
    ->format(fn($value) => $value->diffForHumans()),

Column::make('Actions')
    ->view('components.user-actions')
    ->attributes(['cell_class' => 'text-right']),
```

### Dynamic Icons & Badges

```php
use ModusDigital\LivewireDatatables\Columns\IconColumn;
use ModusDigital\LivewireDatatables\Columns\TextColumn;

protected function columns(): array
{
    return [
        IconColumn::make('status')
            ->icon(fn($record) => $record->active ? 'fa-check' : '<svg></svg>')
            ->count(fn($record) => $record->notifications_count),

        TextColumn::make('role')
            ->badge(fn($record) => $record->role_color), // spans full width
    ];
}
```

### Row Selection & Bulk Actions

```php
class UsersTable extends Table
{
    public bool $enableRowSelection = true;

    protected function bulkActions(): array
    {
        return [
            [
                'name' => 'Delete Selected',
                'key' => 'delete',
                'class' => 'bg-red-600 hover:bg-red-700',
            ],
            [
                'name' => 'Export Selected',
                'key' => 'export',
                'class' => 'bg-green-600 hover:bg-green-700',
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
    }
}
```

### Row Actions

```php
use ModusDigital\LivewireDatatables\Actions\RowAction;

protected function rowActions(): array
{
    return [
        RowAction::make('edit', 'Edit')->icon('<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>'),
        RowAction::make('delete', 'Delete')->class('text-red-600 hover:text-red-900'),
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

```php
use ModusDigital\LivewireDatatables\Actions\Action;

protected function globalActions(): array
{
    return [
        Action::make('create', 'Add User')
            ->class('bg-orange-600 hover:bg-orange-700')
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

### Custom Empty State

```php
public string $emptyStateTitle = 'No users found';
public string $emptyStateDescription = 'Get started by creating your first user.';
```

### Pagination Configuration

```php
public int $perPage = 25;
public array $perPageOptions = [10, 25, 50, 100];
public bool $showPerPageSelector = true;
```

## Customization

### Publishing Views

To customize the table appearance, publish the views:

```bash
php artisan vendor:publish --tag="livewire-datatables-views"
```

This will publish all Blade templates to `resources/views/vendor/livewire-datatables/`.

### Styling

The package uses Tailwind CSS classes exclusively. You can:

1. **Override CSS classes** by modifying the published views
2. **Add custom attributes** to columns using the `attributes()` method
3. **Use custom views** for specific columns with the `view()` method

### Dark Mode

Dark mode is supported out of the box using Tailwind's `dark:` variants. Ensure your project has dark mode configured in `tailwind.config.js`:

```js
module.exports = {
    darkMode: 'class', // or 'media'
    // ... rest of config
}
```

## Testing

The package includes comprehensive tests using Pest 3:

```bash
composer test
```

All traits are individually tested with feature tests covering:
- Column functionality
- Filtering and search
- Sorting mechanisms
- Pagination
- Row selection
- Bulk actions

## Architecture

The package follows a modular architecture using traits:

- **`HasColumns`** - Column management and rendering
- **`HasFilters`** - Filter functionality
- **`HasPagination`** - Pagination configuration
- **`HasSorting`** - Sorting logic
- **`HasRowSelection`** - Row selection and bulk actions
- **`HasRowActions`** - Individual row actions

Each trait is under 150 lines of code and fully unit tested.

## Next Steps

- Browse the traits in `src/Concerns` to understand how each feature works.
- Customize the Blade templates in `resources/views` to match your design.
- Use the `make:table` Artisan command to scaffold new tables from the provided stub.
- Run `composer analyse` and `composer test` to ensure quality as you extend the package.

## Requirements

- PHP 8.3+
- Laravel 10.0+ | 11.0+ | 12.0+
- Livewire 3.0+
- Tailwind CSS 3.0+
- Alpine.js 3.0+

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alex van Steenhoven](https://github.com/modus-digital)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
