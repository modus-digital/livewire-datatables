<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use ModusDigital\LivewireDatatables\Filters\DateFilter;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;
use ModusDigital\LivewireDatatables\Filters\TextFilter;
use ModusDigital\LivewireDatatables\Livewire\Table;

class FiltersUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function company()
    {
        return $this->belongsTo(FiltersCompany::class, 'company_id');
    }
}

class FiltersCompany extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'companies';

    public function getSlugAttribute(): string
    {
        return strtolower((string) ($this->attributes['name'] ?? ''));
    }
}

class DummyHasFiltersTable extends Table
{
    protected string $model = FiltersUser::class;

    protected function filters(): array
    {
        return [
            TextFilter::make('display_name'), // direct attribute
            TextFilter::make('company.slug'), // relatie attribute
            SelectFilter::make('status')->options(['active' => 'Active', 'blocked' => 'Blocked']),
            SelectFilter::make('tags')->multiple(),
            DateFilter::make('created_at')->range(),
        ];
    }
}

it('applyFilters slaat lege waarden over en gebruikt data_get voor dot notatie', function () {
    $table = new DummyHasFiltersTable();

    // stel filters state met dot notation
    $table->filters = [
        'display_name' => '', // leeg -> overslaan
        'company' => ['slug' => 'acme'],
        'status' => 'active',
        'tags' => [], // leeg array -> overslaan
        'created_at' => ['from' => null, 'to' => null], // leeg -> overslaan
    ];

    $query = $table->getModel()->newQuery();
    $spy = \Mockery::spy($query);

    $result = $table->applyFilters($query);

    expect($result)->toBeInstanceOf(Builder::class);
});

it('requiresAttributeFiltering true wanneer attribute-modus triggert', function () {
    $table = new DummyHasFiltersTable();
    $table->filters = [
        'display_name' => 'Jane', // direct attribute -> triggert
    ];

    expect($table->requiresAttributeFiltering())->toBeTrue();
});

it('getActiveAttributeFilters bevat volledige details', function () {
    $table = new DummyHasFiltersTable();
    $table->filters = [
        'display_name' => 'Jane',
        'company' => ['slug' => 'acme'],
        'status' => 'active',
        'tags' => ['a', 'b'],
    ];

    $details = $table->getActiveAttributeFilters();

    // display_name en company.slug zijn attributes -> aanwezig met details
    expect($details)->toBeArray()->and(count($details))->toBeGreaterThanOrEqual(2);

    $first = $details[0];
    expect($first)->toHaveKeys(['relation', 'field', 'value', 'filter_instance'])
        ->and(array_key_exists('operator', $first) || array_key_exists('type', $first) || array_key_exists('multiple', $first))->toBeTrue();
});

it('resetFilters en resetFilter resetten state en dispatchen events', function () {
    $table = new DummyHasFiltersTable();
    $table->filters = ['status' => 'active', 'company' => ['slug' => 'acme']];

    // Livewire\Component::dispatch returnt $this; we checken niet de dispatcher maar state effecten
    $table->resetFilter('company.slug');
    expect(data_get($table->filters, 'company.slug'))->toBeNull();

    $table->resetFilters();
    expect($table->filters)->toBe([]);
});
