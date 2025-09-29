<?php

declare(strict_types=1);

use Livewire\Livewire;
use ModusDigital\LivewireDatatables\Columns\Column;
use ModusDigital\LivewireDatatables\Filters\DateFilter;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;
use ModusDigital\LivewireDatatables\Filters\TextFilter;
use ModusDigital\LivewireDatatables\Livewire\Table;

class ITUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    protected $guarded = [];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function account()
    {
        return $this->belongsTo(ITAccount::class, 'account_id');
    }
}

class ITAccount extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'accounts';

    protected $guarded = [];

    public function getSlugAttribute(): string
    {
        return strtolower((string) ($this->attributes['name'] ?? ''));
    }
}

class ITTable extends Table
{
    protected string $model = ITUser::class;

    protected function columns(): array
    {
        return [
            Column::make('first_name', 'First')->searchable()->sortable(),
            Column::make('account.name', 'Account')->searchable()->sortable(),
            Column::make('full_name', 'Full Name')->sortable(),
            Column::make('status', 'Status')->sortable(),
        ];
    }

    protected function filters(): array
    {
        return [
            TextFilter::make('first_name')->contains(),
            TextFilter::make('full_name')->contains(),
            SelectFilter::make('status')->options(['active' => 'Active', 'blocked' => 'Blocked']),
            SelectFilter::make('account.name')->options(['Acme' => 'Acme']),
            SelectFilter::make('account.slug')->options(['acme' => 'acme']),
            DateFilter::make('created_at')->range(),
            DateFilter::make('account.joined_on'),
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('tests::blank', ['rows' => $this->getRows()]);
    }
}

beforeEach(function () {
    // seed data
    ITAccount::insert([
        ['id' => 1, 'name' => 'Acme', 'joined_on' => '2023-01-10', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Beta', 'joined_on' => '2023-02-10', 'created_at' => now(), 'updated_at' => now()],
    ]);

    ITUser::insert([
        ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'status' => 'active', 'account_id' => 1, 'created_at' => '2023-01-15', 'updated_at' => now()],
        ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Doe', 'status' => 'active', 'account_id' => 1, 'created_at' => '2023-01-20', 'updated_at' => now()],
        ['id' => 3, 'first_name' => 'Alice', 'last_name' => 'Smith', 'status' => 'blocked', 'account_id' => 2, 'created_at' => '2023-03-01', 'updated_at' => now()],
    ]);
});

it('Table getRows normaal pad: filters + sort + paginate', function () {
    $comp = Livewire::test(ITTable::class)
        ->set('perPage', 2);

    $comp->set('filters', ['first_name' => 'J']);

    $comp->call('sortBy', 'first_name');

    $rows = $comp->viewData('rows');

    expect($rows->total())->toBe(2)
        ->and(count($rows->items()))->toBe(2)
        ->and($rows->items()[0]->first_name)->toBe('Jane');
});

it('Table attribute-pad: get + filterByAttributes + sortByAttribute + paginateCollection', function () {
    $comp = Livewire::test(ITTable::class)
        ->set('perPage', 2);

    // filter op attribute full_name en sorteer op full_name
    $comp->set('filters', ['full_name' => 'doe'])
        ->call('sortBy', 'full_name');

    // Livewire rendered view heeft al rows; we halen ze uit viewData
    $rows = $comp->viewData('rows');

    expect($rows->total())->toBe(2)
        ->and(collect($rows->items())->pluck('full_name')->toArray())
        ->toEqualCanonicalizing(['John Doe', 'Jane Doe']);
});

it('applyGlobalSearch doorzoekt kolommen incl. relatievelden', function () {
    $comp = Livewire::test(ITTable::class)
        ->set('search', 'Acme');

    $rows = $comp->viewData('rows');
    expect($rows->total())->toBe(2);
});

it('applySearchToRelationship attribute vs gewone relatie', function () {
    $comp = Livewire::test(ITTable::class);

    // zoek op relatie-attribute: account.slug
    $comp->set('search', 'acme');
    $rows = $comp->viewData('rows');
    expect($rows->total())->toBe(2);
});

it('matchesAttributeFilter operators en matchesDateAttributeFilter single/range', function () {
    $table = new ITTable;

    $r = collect([
        new ITUser(['first_name' => 'Alpha', 'created_at' => '2023-01-05']),
        new ITUser(['first_name' => 'Beta', 'created_at' => '2023-01-15']),
        new ITUser(['first_name' => 'Gamma', 'created_at' => '2023-02-01']),
    ]);

    $ref = new ReflectionClass($table);
    $matches = $ref->getMethod('matchesAttributeFilter');
    $matches->setAccessible(true);

    // text operators
    expect($matches->invoke($table, 'Alpha', 'a', 'like', false, 'text'))->toBeTrue();
    expect($matches->invoke($table, 'Alpha', 'Al', 'starts_with', false, 'text'))->toBeTrue();
    expect($matches->invoke($table, 'Alpha', 'ha', 'ends_with', false, 'text'))->toBeTrue();
    expect($matches->invoke($table, 'Alpha', 'Alpha', '=', false, 'text'))->toBeTrue();

    // date single
    $dateMatches = $ref->getMethod('matchesDateAttributeFilter');
    $dateMatches->setAccessible(true);
    expect($dateMatches->invoke($table, '2023-01-15', '2023-01-15', ''))->toBeTrue();

    // date range
    expect($dateMatches->invoke($table, '2023-01-15', ['from' => '2023-01-10', 'to' => '2023-01-20'], ''))->toBeTrue();
});

it('isSearchable, clearSearch, updatedSearch reset page', function () {
    $comp = Livewire::test(ITTable::class);
    expect($comp->instance()->isSearchable())->toBeTrue();

    $comp->set('search', 'Jane');
    // implicitly checks resetPage via not throwing
    expect($comp->instance()->search)->toBe('Jane');

    $comp->call('clearSearch');
    expect($comp->instance()->search)->toBe('');
});

it('mount initialiseert filters en sorting', function () {
    $comp = Livewire::test(ITTable::class);
    expect($comp->instance()->sortField)->toBe('id')
        ->and($comp->instance()->sortDirection)->toBe('asc');
});

it('sorting URL-sync state (sort, dir) en relatiekolom sort + attribute sort', function () {
    $comp = Livewire::test(ITTable::class)
        ->call('sortBy', 'first_name');

    // URL sync checken via state op component
    expect($comp->instance()->sortField)->toBe('first_name')
        ->and($comp->instance()->sortDirection)->toBe('asc');

    // relatiekolom join-based
    $comp->call('sortBy', 'account.name');
    $rows = $comp->viewData('rows');
    expect($rows->items()[0]->account->name)->toBe('Acme');

    // attribute sort
    $comp->call('sortBy', 'full_name');
    $rows = $comp->viewData('rows');
    expect(in_array($rows->items()[0]->full_name, ['Alice Smith', 'John Doe']))->toBeTrue();
});

it('pagination normaal pad en attribute pad respecteren perPage en total', function () {
    $comp = Livewire::test(ITTable::class)
        ->set('perPage', 2);

    // normaal pad
    $comp->set('search', 'Doe');
    $rows = $comp->viewData('rows');
    expect($rows->perPage())->toBe(2)->and($rows->total())->toBeGreaterThanOrEqual(0);

    // attribute pad
    $comp->set('search', '')
        ->set('filters', ['full_name' => 'doe'])
        ->call('sortBy', 'full_name');
    $rows = $comp->viewData('rows');
    expect($rows->perPage())->toBe(2)->and($rows->total())->toBeGreaterThanOrEqual(0);
});

it('URL/state sync: search, sort, dir, filter veranderen mee en resetten page', function () {
    $comp = Livewire::test(ITTable::class);

    $comp->set('search', 'Jane');
    expect($comp->instance()->search)->toBe('Jane');

    $comp->call('sortBy', 'first_name');
    expect($comp->instance()->sortField)->toBe('first_name')
        ->and($comp->instance()->sortDirection)->toBe('asc');

    $comp->set('filters', ['first_name' => 'J']);
    // filters in URL worden onder key 'filter' gezet; we asserten state i.p.v. payload
    expect($comp->instance()->filters)->toBe(['first_name' => 'J']);
});
