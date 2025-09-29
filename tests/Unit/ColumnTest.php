<?php

declare(strict_types=1);

use ModusDigital\LivewireDatatables\Columns\Column;

enum Suit: string
{
    case Hearts = 'H';
    case Spades = 'S';
}
enum Status
{
    case Active;
    case Inactive;
}

it('constructor label/field gedrag zonder label', function () {
    $column = new Column('full_name');

    expect($column->getName())->toBe('Full Name')
        ->and($column->getField())->toBe('full_name');
});

it('constructor label/field gedrag met label', function () {
    $column = new Column('user.name', 'Customer');

    expect($column->getName())->toBe('Customer')
        ->and($column->getField())->toBe('user.name');
});

it('getValue zet BackedEnum naar value en UnitEnum naar name', function () {
    $record = (object) [
        'backed' => Suit::Hearts,
        'unit' => Status::Active,
    ];

    $backed = Column::make('backed');
    $unit = Column::make('unit');

    expect($backed->getValue($record))->toBe('H')
        ->and($unit->getValue($record))->toBe('Active');
});

it('format callback wordt toegepast in getValue()', function () {
    $record = (object) ['amount' => 1234.5];

    $column = Column::make('amount')->format(function ($value) {
        return number_format((float) $value, 2, '.', '');
    });

    expect($column->getValue($record))->toBe('1234.50');
});
