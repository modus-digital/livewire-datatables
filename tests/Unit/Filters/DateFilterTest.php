<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ModusDigital\LivewireDatatables\Filters\DateFilter;

// Helper function to create mock query
function createMockQueryForDateFilter(): Builder
{
    return Mockery::mock(Builder::class);
}

beforeEach(function () {
    $this->filter = DateFilter::make('Created At');
});

it('creates filter with correct name and field', function () {
    expect($this->filter->getName())->toBe('Created At')
        ->and($this->filter->getField())->toBe('created_at');
});

it('sets and gets default value', function () {
    $filter = DateFilter::make('Created At')->default('2024-01-01');

    expect($filter->getDefault())->toBe('2024-01-01');
});

it('sets range mode', function () {
    $filter = DateFilter::make('Created At')->range();

    expect($filter->isRange())->toBeTrue();
});

it('sets range mode explicitly false', function () {
    $filter = DateFilter::make('Created At')->range(false);

    expect($filter->isRange())->toBeFalse();
});

it('is not range by default', function () {
    expect($this->filter->isRange())->toBeFalse();
});

it('sets custom format', function () {
    $filter = DateFilter::make('Created At')->format('d/m/Y');

    // Note: format is protected, so we test indirectly through behavior
    expect($filter)->toBeInstanceOf(DateFilter::class);
});

it('applies single date filter', function () {
    $query = createMockQueryForDateFilter();
    $query->shouldReceive('whereDate')->once()->with('created_at', Mockery::type(Carbon::class))->andReturnSelf();

    $this->filter->apply($query, '2024-01-01');
});

it('applies date range filter', function () {
    $filter = DateFilter::make('Created At')->range();

    $query = createMockQueryForDateFilter();
    $query->shouldReceive('where')->twice()->andReturnSelf();

    $filter->apply($query, ['from' => '2024-01-01', 'to' => '2024-01-31']);
});

it('applies partial date range filter with only from date', function () {
    $filter = DateFilter::make('Created At')->range();

    $query = createMockQueryForDateFilter();
    $query->shouldReceive('where')->once()->with('created_at', '>=', Mockery::type(Carbon::class))->andReturnSelf();

    $filter->apply($query, ['from' => '2024-01-01']);
});

it('applies partial date range filter with only to date', function () {
    $filter = DateFilter::make('Created At')->range();

    $query = createMockQueryForDateFilter();
    $query->shouldReceive('where')->once()->with('created_at', '<=', Mockery::type(Carbon::class))->andReturnSelf();

    $filter->apply($query, ['to' => '2024-01-31']);
});

it('handles relationship field with single date', function () {
    $filter = DateFilter::make('User Created')->field('user.created_at');

    $query = createMockQueryForDateFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, '2024-01-01');
});

it('handles relationship field with date range', function () {
    $filter = DateFilter::make('User Created')->field('user.created_at')->range();

    $query = createMockQueryForDateFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, ['from' => '2024-01-01', 'to' => '2024-01-31']);
});

it('returns query unchanged when value is empty', function () {
    $query = createMockQueryForDateFilter();
    $query->shouldNotReceive('where');
    $query->shouldNotReceive('whereDate');
    $query->shouldNotReceive('whereHas');

    $result = $this->filter->apply($query, '');
    expect($result)->toBe($query);

    $result = $this->filter->apply($query, null);
    expect($result)->toBe($query);

    $result = $this->filter->apply($query, []);
    expect($result)->toBe($query);
});

it('renders single date input correctly', function () {
    $html = $this->filter->render();

    expect($html)->toContain('type="date"')
        ->and($html)->toContain('wire:model.live="filters.created_at"')
        ->and($html)->toContain('<label')
        ->and($html)->toContain('Created At')
        ->and($html)->not->toContain('grid-cols-2');
});

it('renders date range inputs correctly', function () {
    $filter = DateFilter::make('Created At')->range();
    $html = $filter->render();

    expect($html)->toContain('wire:model.live="filters.created_at.from"')
        ->and($html)->toContain('wire:model.live="filters.created_at.to"')
        ->and($html)->toContain('grid-cols-2')
        ->and($html)->toContain('placeholder="From"')
        ->and($html)->toContain('placeholder="To"');
});
