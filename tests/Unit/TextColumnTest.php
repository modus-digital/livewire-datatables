<?php

use ModusDigital\LivewireDatatables\Columns\TextColumn;

enum Status: string
{
    case OPEN = 'open';
}

it('limits text and casts enums', function () {
    $column = TextColumn::make('Status')->limit(3);

    $record = new class
    {
        public Status $status = Status::OPEN;
    };

    expect($column->getValue($record))->toBe('ope...');
});

it('renders badge using callback and field fallback', function () {
    $column = TextColumn::make('role')->badge(fn($record) => $record->color);

    $record = new class
    {
        public string $role = 'Admin';

        public string $color = 'green';
    };

    $value = $column->getValue($record);

    expect($value)->toContain('bg-green-100')
        ->and($value)->toContain('Admin');
});

describe('TextColumn Enhanced Usage', function () {
    it('creates text column with field and label parameters', function () {
        $column = TextColumn::make('user_role', 'User Role');

        expect($column->getName())->toBe('User Role')
            ->and($column->getField())->toBe('user_role');
    });

    it('supports label method chaining', function () {
        $column = TextColumn::make('status')->label('Order Status');

        expect($column->getName())->toBe('Order Status')
            ->and($column->getField())->toBe('status');
    });

    it('combines enhanced constructor with TextColumn methods', function () {
        $column = TextColumn::make('description', 'Product Description')
            ->limit(50)
            ->badge('blue');

        $record = new class {
            public string $description = 'This is a very long product description that should be truncated';
        };

        expect($column->getName())->toBe('Product Description')
            ->and($column->getField())->toBe('description');

        $value = $column->getValue($record);
        expect($value)->toContain('bg-blue-100')
            ->and($value)->toContain('This is a very long product description that shoul...');
    });

    it('supports method chaining with label and field', function () {
        $column = TextColumn::make('original_field')
            ->label('Custom Label')
            ->field('actual_field')
            ->limit(25);

        $record = new class {
            public string $actual_field = 'This is a test value for the field';
        };

        expect($column->getName())->toBe('Custom Label')
            ->and($column->getField())->toBe('actual_field');

        $value = $column->getValue($record);
        expect($value)->toBe('This is a test value for...');
    });
});
