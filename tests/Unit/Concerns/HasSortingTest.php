<?php

declare(strict_types=1);

use ModusDigital\LivewireDatatables\Columns\Column;
use ModusDigital\LivewireDatatables\Livewire\Table;

class HSUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function account()
    {
        return $this->belongsTo(HSAccount::class, 'account_id');
    }
}

class HSAccount extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'accounts';
}

class DummyHasSortingTable extends Table
{
    protected string $model = HSUser::class;

    protected function columns(): array
    {
        return [
            Column::make('first_name', 'First')->sortable(),
            Column::make('account.name', 'Account Name')->sortable(),
            Column::make('full_name', 'Full Name')->sortable(), // attribute
            Column::make('last_name', 'Last'), // non-sortable
        ];
    }
}

it('sortBy togglet richting en wijzigt veld; non-sortable doet niets', function () {
    $t = new DummyHasSortingTable;
    $t->sortBy('first_name');
    expect($t->sortField)->toBe('first_name')->and($t->sortDirection)->toBe('asc');

    $t->sortBy('first_name');
    expect($t->sortDirection)->toBe('desc');

    $t->sortBy('account.name');
    expect($t->sortField)->toBe('account.name')->and($t->sortDirection)->toBe('asc');

    // non-sortable
    $t->sortBy('last_name');
    expect($t->sortField)->toBe('account.name');
});

it('applySorting gewone kolom prefix met tabelnaam', function () {
    $t = new DummyHasSortingTable;
    $t->sortBy('first_name');
    $query = (new HSUser)->newQuery();
    $t->applySorting($query);
    expect($query->toSql())->toContain('order by "users"."first_name" asc');
});

it('applySorting relatie join (BelongsTo) en één JOIN per tabel', function () {
    $t = new DummyHasSortingTable;
    $t->sortBy('account.name');
    $query = (new HSUser)->newQuery();
    $t->applySorting($query);
    $sql = $query->toSql();
    expect($sql)->toContain('left join "accounts"')
        ->and($sql)->toContain('order by "accounts"."name" asc');

    // opnieuw toepassen moet geen extra join toevoegen
    $t->applySorting($query);
    $sql2 = $query->toSql();
    // tel aantal "left join \"accounts\"" occurrences gelijk
    $count1 = substr_count($sql, 'left join "accounts"');
    $count2 = substr_count($sql2, 'left join "accounts"');
    expect($count2)->toBe($count1);
});

it('applySorting attribute-pad zet requiresAttributeSorting op true en geen SQL orderBy', function () {
    $t = new DummyHasSortingTable;
    $t->sortBy('full_name');
    $query = (new HSUser)->newQuery();
    $t->applySorting($query);
    expect($t->requiresAttributeSorting())->toBeTrue()
        ->and($query->toSql())->not->toContain('order by');
});

it('initializeSorting zet defaults en getSortIcon/isSorted werken', function () {
    $t = new DummyHasSortingTable;
    $t->initializeSorting();
    expect($t->sortField)->toBe('id')->and($t->sortDirection)->toBe('asc');

    expect($t->getSortIcon('first_name'))->toBe('sort')
        ->and($t->isSorted('first_name'))->toBeFalse();

    $t->sortBy('first_name');
    expect($t->getSortIcon('first_name'))->toBe('sort-asc')
        ->and($t->isSorted('first_name'))->toBeTrue();

    $t->sortBy('first_name');
    expect($t->getSortIcon('first_name'))->toBe('sort-desc');
});
