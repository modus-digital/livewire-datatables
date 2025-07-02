<?php

use Illuminate\Database\Eloquent\Builder;
use ModusDigital\LivewireDatatables\Filters\TextFilter;

// Helper function to create mock query
function createMockQueryForTextFilter(): Builder
{
    return Mockery::mock(Builder::class);
}

beforeEach(function () {
    $this->filter = TextFilter::make('Name');
});

it('creates filter with correct name and field', function () {
    expect($this->filter->getName())->toBe('Name')
        ->and($this->filter->getField())->toBe('name');
});

it('allows custom field name', function () {
    $filter = TextFilter::make('Full Name')->field('full_name');

    expect($filter->getField())->toBe('full_name');
});

it('sets and gets default value', function () {
    $filter = TextFilter::make('Name')->default('John');

    expect($filter->getDefault())->toBe('John');
});

it('sets and gets placeholder', function () {
    $filter = TextFilter::make('Name')->placeholder('Enter name...');

    expect($filter->getPlaceholder())->toBe('Enter name...');
});

it('uses exact operator', function () {
    $filter = TextFilter::make('Name')->exact();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('where')->once()->with('name', '=', 'John')->andReturnSelf();

    $filter->apply($query, 'John');
});

it('uses contains operator by default', function () {
    $query = createMockQueryForTextFilter();
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();

    $this->filter->apply($query, 'John');
});

it('uses contains operator explicitly', function () {
    $filter = TextFilter::make('Name')->contains();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();

    $filter->apply($query, 'John');
});

it('uses starts with operator', function () {
    $filter = TextFilter::make('Name')->startsWith();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('where')->once()->with('name', 'like', 'John%')->andReturnSelf();

    $filter->apply($query, 'John');
});

it('uses ends with operator', function () {
    $filter = TextFilter::make('Name')->endsWith();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('where')->once()->with('name', 'like', '%John')->andReturnSelf();

    $filter->apply($query, 'John');
});

it('handles relationship fields with exact operator', function () {
    $filter = TextFilter::make('User Name')->field('user.name')->exact();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, 'John');
});

it('handles relationship fields with like operator', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, 'John');
});

it('handles relationship fields with starts with operator', function () {
    $filter = TextFilter::make('User Name')->field('user.name')->startsWith();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, 'John');
});

it('handles relationship fields with ends with operator', function () {
    $filter = TextFilter::make('User Name')->field('user.name')->endsWith();

    $query = createMockQueryForTextFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, 'John');
});

it('returns query unchanged when value is empty', function () {
    $query = createMockQueryForTextFilter();
    $query->shouldNotReceive('where');
    $query->shouldNotReceive('whereHas');

    $result = $this->filter->apply($query, '');
    expect($result)->toBe($query);

    $result = $this->filter->apply($query, null);
    expect($result)->toBe($query);
});

it('renders HTML input correctly', function () {
    $html = $this->filter->render();

    expect($html)->toContain('wire:model.live.debounce.300ms="filters.name"')
        ->and($html)->toContain('placeholder="Filter by Name"')
        ->and($html)->toContain('<label')
        ->and($html)->toContain('Name')
        ->and($html)->toContain('<input');
});

it('renders HTML with custom placeholder', function () {
    $filter = TextFilter::make('Name')->placeholder('Search names...');
    $html = $filter->render();

    expect($html)->toContain('placeholder="Search names..."');
});
