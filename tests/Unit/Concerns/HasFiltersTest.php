<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Concerns\HasFilters;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;
use ModusDigital\LivewireDatatables\Filters\TextFilter;

beforeEach(function () {
    $this->component = new class
    {
        use HasFilters;

        public $resetPageCalled = false;

        protected function filters(): array
        {
            return [
                TextFilter::make('Name')->default('John'),
                SelectFilter::make('Status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
                TextFilter::make('Email')->field('email'),
            ];
        }

        public function resetPage(): void
        {
            $this->resetPageCalled = true;
        }
    };
});

it('returns empty filters by default', function () {
    $component = new class
    {
        use HasFilters;
    };

    expect($component->getFilters())->toBeEmpty();
});

it('returns defined filters', function () {
    $filters = $this->component->getFilters();

    expect($filters)->toHaveCount(3)
        ->and($filters->first())->toBeInstanceOf(TextFilter::class)
        ->and($filters->first()->getName())->toBe('Name')
        ->and($filters->get(1))->toBeInstanceOf(SelectFilter::class)
        ->and($filters->get(1)->getName())->toBe('Status');
});

it('caches filters after first call', function () {
    $filters1 = $this->component->getFilters();
    $filters2 = $this->component->getFilters();

    expect($filters1)->toBe($filters2);
});

it('applies filters to query', function () {
    $this->component->filters = [
        'name' => 'John',
        'status' => 'active',
    ];

    $model = new class extends Model { protected $table = 'test_table'; };
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldReceive('where')->twice()->andReturnSelf();

    $result = $this->component->applyFilters($query);

    expect($result)->toBe($query);
});

it('skips empty filter values', function () {
    $this->component->filters = [
        'name' => '',
        'status' => null,
        'email' => [],
    ];

    $model = new class extends Model { protected $table = 'test_table'; };
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldNotReceive('where');

    $result = $this->component->applyFilters($query);

    expect($result)->toBe($query);
});

it('resets all filters', function () {
    $this->component->filters = ['name' => 'John', 'status' => 'active'];

    $this->component->resetFilters();

    expect($this->component->filters)->toBe([])
        ->and($this->component->resetPageCalled)->toBeTrue();
});

it('resets specific filter', function () {
    $this->component->filters = ['name' => 'John', 'status' => 'active'];

    $this->component->resetFilter('name');

    expect($this->component->filters)->toBe(['status' => 'active'])
        ->and($this->component->resetPageCalled)->toBeTrue();
});

it('detects active filters', function () {
    $this->component->filters = ['name' => 'John'];

    expect($this->component->hasActiveFilters())->toBeTrue();
});

it('detects no active filters when empty', function () {
    $this->component->filters = [];

    expect($this->component->hasActiveFilters())->toBeFalse();
});

it('detects no active filters when values are empty', function () {
    $this->component->filters = ['name' => '', 'status' => null, 'tags' => []];

    expect($this->component->hasActiveFilters())->toBeFalse();
});

it('counts active filters', function () {
    $this->component->filters = ['name' => 'John', 'status' => 'active', 'email' => ''];

    expect($this->component->getActiveFilterCount())->toBe(2);
});

it('counts zero active filters', function () {
    $this->component->filters = ['name' => '', 'status' => null];

    expect($this->component->getActiveFilterCount())->toBe(0);
});

it('initializes filter defaults', function () {
    $this->component->initializeFilters();

    expect($this->component->filters['name'])->toBe('John');
});

it('does not override existing filter values during initialization', function () {
    $this->component->filters = ['name' => 'Jane'];

    $this->component->initializeFilters();

    expect($this->component->filters['name'])->toBe('Jane');
});

it('resets page when filters are updated', function () {
    $this->component->updatedFilters();

    expect($this->component->resetPageCalled)->toBeTrue();
});
