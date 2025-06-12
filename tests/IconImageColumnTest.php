<?php

use ModusDigital\LivewireDatatables\Columns\IconColumn;
use ModusDigital\LivewireDatatables\Columns\ImageColumn;

it('renders icon with count and image shapes', function () {
    $iconColumn = IconColumn::make('Icon')->icon('<svg></svg>')->count('total');

    $record = new class
    {
        public int $total = 5;

        public string $avatar = 'a.png';
    };

    expect($iconColumn->getValue($record))->toContain('5');

    $image = ImageColumn::make('Avatar')->field('avatar')->circle();

    expect($image->getValue($record))->toContain('rounded-full');
});

it('supports callback icons and counts', function () {
    $column = IconColumn::make('status')
        ->icon(fn ($record) => $record->active ? 'fa-check' : '<svg></svg>')
        ->count(fn ($record) => $record->total);

    $record = new class
    {
        public bool $active = true;

        public int $total = 2;
    };

    $value = $column->getValue($record);

    expect($value)->toContain('<i class="fa-check"></i>')
        ->and($value)->toContain('2');
});
