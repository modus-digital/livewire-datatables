<?php

use ModusDigital\LivewireDatatables\Columns\IconColumn;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;

beforeEach(function () {
    $this->component = new class
    {
        use HasColumns;

        protected function columns(): array
        {
            return [
                TextColumn::make('Name')->searchable()->sortable(),
                TextColumn::make('Email')->searchable(),
                IconColumn::make('Status')->sortable(),
                TextColumn::make('Created At')->field('created_at'),
            ];
        }
    };
});

it('returns empty columns by default', function () {
    $component = new class
    {
        use HasColumns;
    };

    expect($component->getColumns())->toBeEmpty();
});

it('returns defined columns', function () {
    $columns = $this->component->getColumns();

    expect($columns)->toHaveCount(4)
        ->and($columns->first())->toBeInstanceOf(TextColumn::class)
        ->and($columns->first()->getName())->toBe('Name')
        ->and($columns->get(2))->toBeInstanceOf(IconColumn::class);
});

it('caches columns after first call', function () {
    $columns1 = $this->component->getColumns();
    $columns2 = $this->component->getColumns();

    expect($columns1)->toBe($columns2);
});

it('gets searchable columns only', function () {
    $searchableColumns = $this->component->getSearchableColumns();

    expect($searchableColumns)->toHaveCount(2)
        ->and($searchableColumns->first()->getName())->toBe('Name')
        ->and($searchableColumns->last()->getName())->toBe('Email');
});

it('gets sortable columns only', function () {
    $sortableColumns = $this->component->getSortableColumns();

    expect($sortableColumns)->toHaveCount(2)
        ->and($sortableColumns->first()->getName())->toBe('Name')
        ->and($sortableColumns->last()->getName())->toBe('Status');
});

it('finds column by field', function () {
    $column = $this->component->getColumn('name');

    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('Name');
});

it('finds column by custom field', function () {
    $column = $this->component->getColumn('created_at');

    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('Created At');
});

it('returns null for non-existent column', function () {
    $column = $this->component->getColumn('non_existent');

    expect($column)->toBeNull();
});

it('checks if column is sortable', function () {
    expect($this->component->isColumnSortable('name'))->toBeTrue()
        ->and($this->component->isColumnSortable('email'))->toBeFalse()
        ->and($this->component->isColumnSortable('status'))->toBeTrue()
        ->and($this->component->isColumnSortable('non_existent'))->toBeFalse();
});

it('detects when columns exist', function () {
    expect($this->component->hasColumns())->toBeTrue();
});

it('detects when no columns exist', function () {
    $component = new class
    {
        use HasColumns;
    };

    expect($component->hasColumns())->toBeFalse();
});
