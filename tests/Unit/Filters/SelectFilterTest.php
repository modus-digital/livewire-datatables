<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;

// Helper function to create mock query
function createMockQueryForSelectFilter(): Builder
{
    $model = new class extends Model
    {
        protected $table = 'test_table';
    };

    $mock = Mockery::mock(Builder::class);
    $mock->shouldReceive('getModel')->andReturn($model)->byDefault();

    return $mock;
}

beforeEach(function () {
    $this->filter = SelectFilter::make('Status');
});

it('creates filter with correct name and field', function () {
    expect($this->filter->getName())->toBe('Status')
        ->and($this->filter->getField())->toBe('status');
});

it('sets and gets options', function () {
    $options = ['active' => 'Active', 'inactive' => 'Inactive'];
    $filter = SelectFilter::make('Status')->options($options);

    expect($filter->getOptions())->toBe($options);
});

it('sets multiple mode', function () {
    $filter = SelectFilter::make('Status')->multiple();

    expect($filter->isMultiple())->toBeTrue();
});

it('sets multiple mode explicitly false', function () {
    $filter = SelectFilter::make('Status')->multiple(false);

    expect($filter->isMultiple())->toBeFalse();
});

it('is not multiple by default', function () {
    expect($this->filter->isMultiple())->toBeFalse();
});

it('applies single value filter', function () {
    $query = createMockQueryForSelectFilter();
    $query->shouldReceive('where')->once()->with('test_table.status', 'active')->andReturnSelf();

    $this->filter->apply($query, 'active');
});

it('applies multiple values filter', function () {
    $filter = SelectFilter::make('Status')->multiple();

    $query = createMockQueryForSelectFilter();
    $query->shouldReceive('whereIn')->once()->with('test_table.status', ['active', 'pending'])->andReturnSelf();

    $filter->apply($query, ['active', 'pending']);
});

it('handles relationship field with single value', function () {
    $filter = SelectFilter::make('User Status')->field('user.status');

    $query = createMockQueryForSelectFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, 'active');
});

it('properly qualifies relationship field in whereHas closure', function () {
    $filter = SelectFilter::make('Client Status')->field('client.status');

    // Create a more detailed mock that can verify the closure behavior
    $query = createMockQueryForSelectFilter();

    $query->shouldReceive('whereHas')->once()->with('client', Mockery::on(function ($closure) {
        // Create a mock for the sub-query within the whereHas closure
        $relatedModel = new class extends Model
        {
            protected $table = 'clients';
        };

        $subQuery = Mockery::mock(Builder::class);
        $subQuery->shouldReceive('getModel')->andReturn($relatedModel);
        $subQuery->shouldReceive('where')->once()->with('clients.status', 'active')->andReturnSelf();

        // Execute the closure to verify it calls the right methods
        $closure($subQuery);

        return true;
    }))->andReturnSelf();

    $filter->apply($query, 'active');
});

it('handles relationship field with multiple values', function () {
    $filter = SelectFilter::make('User Status')->field('user.status')->multiple();

    $query = createMockQueryForSelectFilter();
    $query->shouldReceive('whereHas')->once()->with('user', Mockery::type('Closure'))->andReturnSelf();

    $filter->apply($query, ['active', 'pending']);
});

it('returns query unchanged when value is empty', function () {
    $query = createMockQueryForSelectFilter();
    $query->shouldNotReceive('where');
    $query->shouldNotReceive('whereIn');
    $query->shouldNotReceive('whereHas');

    $result = $this->filter->apply($query, '');
    expect($result)->toBe($query);

    $result = $this->filter->apply($query, []);
    expect($result)->toBe($query);

    $result = $this->filter->apply($query, null);
    expect($result)->toBe($query);
});

it('renders single select HTML correctly', function () {
    $filter = SelectFilter::make('Status')->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);

    $html = $filter->render();

    expect($html)->toContain('wire:model.live="filters.status"')
        ->and($html)->toContain('<select')
        ->and($html)->toContain('<option value="">Select Status</option>')
        ->and($html)->toContain('<option value="active">Active</option>')
        ->and($html)->toContain('<option value="inactive">Inactive</option>')
        ->and($html)->not->toContain('multiple');
});

it('renders multiple select HTML correctly', function () {
    $filter = SelectFilter::make('Status')
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->multiple();

    $html = $filter->render();

    expect($html)->toContain('multiple')
        ->and($html)->not->toContain('<option value="">Select Status</option>');
});

it('renders HTML with custom placeholder', function () {
    $filter = SelectFilter::make('Status')
        ->options(['active' => 'Active'])
        ->placeholder('Choose status...');

    $html = $filter->render();

    expect($html)->toContain('<option value="">Choose status...</option>');
});
