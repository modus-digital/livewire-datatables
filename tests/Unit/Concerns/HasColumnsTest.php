<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Columns\Column;
use ModusDigital\LivewireDatatables\Livewire\Table;

class DummyUserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getFullNameAttribute(): string
    {
        return ($this->first_name ?? '') . ' ' . ($this->last_name ?? '');
    }

    public function account()
    {
        return $this->belongsTo(DummyAccountModel::class, 'account_id');
    }
}

class DummyAccountModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'accounts';

    public function getLabelAttribute(): string
    {
        return (string) ($this->attributes['label'] ?? '');
    }
}

class DummyHasColumnsTable extends Table
{
    protected string $model = DummyUserModel::class;

    protected function columns(): array
    {
        return [
            Column::make('first_name')->searchable(),
            Column::make('last_name')->hidden(),
            Column::make('account.label', 'Account'),
            Column::make('account_id', 'Account Id')->sortable(),
        ];
    }
}

beforeEach(function () {
    ModusDigital\LivewireDatatables\Concerns\HasColumns::clearAttributeDetectionCache();
});

it('getColumns cache en hidden filtering', function () {
    $table = new DummyHasColumnsTable();

    $first = $table->getColumns();
    $second = $table->getColumns();

    expect($first)->toBeInstanceOf(Collection::class)
        ->and($first->count())->toBe(3) // hidden last_name is gefilterd
        ->and($first)->toBe($second); // cache gebruikt
});

it('getColumn vindt kolommen ook bij relatie/dot notatie', function () {
    $table = new DummyHasColumnsTable();

    expect($table->getColumn('first_name'))->not->toBeNull()
        ->and($table->getColumn('account.label'))->not->toBeNull()
        ->and($table->getColumn('account'))->not->toBeNull(); // relatie root
});

it('isFieldAttribute detectie en cache (direct en relatie)', function () {
    $table = new DummyHasColumnsTable();

    // direct accessor op DummyUserModel
    expect($table->isFieldAttribute('full_name'))->toBeTrue();

    // relatie attribute op DummyAccountModel
    expect($table->isFieldAttribute('account.label'))->toBeTrue();

    // non-attribute
    expect($table->isFieldAttribute('first_name'))->toBeFalse();

    // cache pad (call nogmaals)
    expect($table->isFieldAttribute('full_name'))->toBeTrue();
});

it('renderCell gebruikt view als set', function () {
    $table = new DummyHasColumnsTable();
    $record = new DummyUserModel();
    $record->first_name = 'Jane';
    $column = Column::make('first_name')->view('tests::stub-cell');

    $html = $table->renderCell($column, $record);

    expect($html)->toContain('Jane');
});
