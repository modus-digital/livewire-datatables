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
