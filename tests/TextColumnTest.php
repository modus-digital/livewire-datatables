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
    $column = TextColumn::make('role')->badge(fn ($record) => $record->color);

    $record = new class {
        public string $role = 'Admin';
        public string $color = 'green';
    };

    $value = $column->getValue($record);

    expect($value)->toContain('bg-green-100')
        ->and($value)->toContain('Admin');
});
