<?php

use ModusDigital\LivewireDatatables\Columns\Column;

describe('Column Enhanced Usage', function () {
    it('creates column with field name only (original behavior)', function () {
        $column = Column::make('user_name');

        expect($column->getName())->toBe('User Name')
            ->and($column->getField())->toBe('user_name');
    });

    it('creates column with field and label parameters', function () {
        $column = Column::make('user_name', 'Full Name');

        expect($column->getName())->toBe('Full Name')
            ->and($column->getField())->toBe('user_name');
    });

    it('supports label method chaining', function () {
        $column = Column::make('user_name')->label('Custom Label');

        expect($column->getName())->toBe('Custom Label')
            ->and($column->getField())->toBe('user_name');
    });

    it('supports field method chaining', function () {
        $column = Column::make('display_name')->field('custom_field');

        expect($column->getName())->toBe('Display Name')
            ->and($column->getField())->toBe('custom_field');
    });

    it('supports combined label and field method chaining', function () {
        $column = Column::make('original_field')
            ->label('Custom Label')
            ->field('custom_field');

        expect($column->getName())->toBe('Custom Label')
            ->and($column->getField())->toBe('custom_field');
    });

    it('label method overrides constructor label', function () {
        $column = Column::make('field_name', 'Constructor Label')
            ->label('Method Label');

        expect($column->getName())->toBe('Method Label')
            ->and($column->getField())->toBe('field_name');
    });

    it('field method overrides constructor field', function () {
        $column = Column::make('constructor_field', 'Label')
            ->field('method_field');

        expect($column->getName())->toBe('Label')
            ->and($column->getField())->toBe('method_field');
    });

    it('maintains method chaining fluency', function () {
        $column = Column::make('field')
            ->label('Custom Label')
            ->field('custom_field')
            ->sortable()
            ->searchable()
            ->width('100px')
            ->align('center');

        expect($column->getName())->toBe('Custom Label')
            ->and($column->getField())->toBe('custom_field')
            ->and($column->isSortable())->toBeTrue()
            ->and($column->isSearchable())->toBeTrue()
            ->and($column->getWidth())->toBe('100px')
            ->and($column->getAlign())->toBe('center');
    });

    it('handles edge cases with empty or special characters', function () {
        $column = Column::make('field_with_underscores', 'Label With Spaces & Special!');

        expect($column->getName())->toBe('Label With Spaces & Special!')
            ->and($column->getField())->toBe('field_with_underscores');
    });

    it('supports dot notation for relationships in field', function () {
        $column = Column::make('user.profile.name', 'User Profile Name');

        expect($column->getName())->toBe('User Profile Name')
            ->and($column->getField())->toBe('user.profile.name');
    });
});

describe('Column Backward Compatibility', function () {
    it('maintains original constructor behavior', function () {
        $column = Column::make('test_field');

        expect($column->getName())->toBe('Test Field')
            ->and($column->getField())->toBe('test_field');
    });

    it('maintains original field method behavior', function () {
        $column = Column::make('original')->field('new_field');

        expect($column->getName())->toBe('Original')
            ->and($column->getField())->toBe('new_field');
    });

    it('maintains all existing method chaining', function () {
        $column = Column::make('test')
            ->sortable()
            ->searchable()
            ->hidden()
            ->width('50px')
            ->align('right');

        expect($column->isSortable())->toBeTrue()
            ->and($column->isSearchable())->toBeTrue()
            ->and($column->isHidden())->toBeTrue()
            ->and($column->getWidth())->toBe('50px')
            ->and($column->getAlign())->toBe('right');
    });
});

describe('Column Value Extraction', function () {
    it('extracts values correctly with new constructor pattern', function () {
        $column = Column::make('user_name', 'Full Name');

        $record = new class {
            public string $user_name = 'John Doe';
        };

        expect($column->getValue($record))->toBe('John Doe');
    });

    it('extracts values correctly with label method', function () {
        $column = Column::make('user_name')->label('Full Name');

        $record = new class {
            public string $user_name = 'Jane Smith';
        };

        expect($column->getValue($record))->toBe('Jane Smith');
    });

    it('extracts values correctly with field method', function () {
        $column = Column::make('display_name')->field('actual_field');

        $record = new class {
            public string $actual_field = 'Test Value';
        };

        expect($column->getValue($record))->toBe('Test Value');
    });
});
