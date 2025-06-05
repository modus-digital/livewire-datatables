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
