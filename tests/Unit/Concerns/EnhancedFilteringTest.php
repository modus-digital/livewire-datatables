<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ModusDigital\LivewireDatatables\Filters\TextFilter;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;
use ModusDigital\LivewireDatatables\Filters\DateFilter;

// Test models
class FilteringTestModel extends Model
{
    protected $table = 'filtering_test_models';

    protected $appends = ['full_name', 'status_label', 'computed_date'];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'json',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Active User',
            'inactive' => 'Inactive User',
            default => 'Unknown Status'
        };
    }

    public function getComputedDateAttribute(): string
    {
        return date('Y-m-d');
    }

    public function relatedModel()
    {
        return $this->belongsTo(RelatedFilteringModel::class);
    }
}

class RelatedFilteringModel extends Model
{
    protected $table = 'related_filtering_models';

    protected $appends = ['computed_name'];

    public function getComputedNameAttribute(): string
    {
        return strtoupper($this->name);
    }
}

beforeEach(function () {
    $this->testModel = new FilteringTestModel();
});

describe('enhanced text filtering with attribute detection', function () {
    it('applies SQL filtering for regular database columns', function () {
        $filter = TextFilter::make('Name')->field('name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('where')->once()->with('filtering_test_models.name', 'like', '%john%')->andReturnSelf();

        $result = $filter->apply($query, 'john');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeFalse();
    });

    it('flags attribute filtering for model attributes', function () {
        // Mock Schema to return empty columns so full_name is detected as attribute
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = TextFilter::make('Full Name')->field('full_name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Should NOT call where since it's an attribute

        $result = $filter->apply($query, 'john doe');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['field'])->toBe('full_name')
            ->and($details['value'])->toBe('john doe')
            ->and($details['relation'])->toBeNull();
    });

    it('handles different text filter operators for attributes', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = TextFilter::make('Full Name')->field('full_name')->exact();

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);

        $result = $filter->apply($query, 'John Doe');

        expect($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['operator'])->toBe('=');
    });

    it('applies SQL filtering for relationship database columns', function () {
        $filter = TextFilter::make('Related Name')->field('relatedModel.name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('whereHas')->once()->andReturnSelf();

        $result = $filter->apply($query, 'test');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeFalse();
    });

    it('flags attribute filtering for relationship attributes', function () {
        $filter = TextFilter::make('Related Computed')->field('relatedModel.computed_name');

        // Mock the relationship
        $relatedModel = new RelatedFilteringModel();
        $relationInstance = Mockery::mock(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        $relationInstance->shouldReceive('getRelated')->andReturn($relatedModel);

        $mockModel = Mockery::mock(FilteringTestModel::class)->makePartial();
        $mockModel->shouldReceive('relatedModel')->andReturn($relationInstance);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($mockModel);

        $result = $filter->apply($query, 'TEST');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['relation'])->toBe('relatedModel')
            ->and($details['field'])->toBe('computed_name');
    });
});

describe('enhanced select filtering with attribute detection', function () {
    it('applies SQL filtering for regular database columns', function () {
        $filter = SelectFilter::make('Status')->field('status')->options(['active' => 'Active', 'inactive' => 'Inactive']);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('where')->once()->with('filtering_test_models.status', 'active')->andReturnSelf();

        $result = $filter->apply($query, 'active');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeFalse();
    });

    it('flags attribute filtering for model attributes', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = SelectFilter::make('Status Label')->field('status_label')->options(['Active User' => 'Active', 'Inactive User' => 'Inactive']);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);

        $result = $filter->apply($query, 'Active User');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeTrue();
    });

    it('handles multiple select for attributes', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = SelectFilter::make('Status Label')->field('status_label')->multiple()->options(['Active User' => 'Active', 'Inactive User' => 'Inactive']);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);

        $result = $filter->apply($query, ['Active User', 'Inactive User']);

        expect($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['multiple'])->toBeTrue()
            ->and($details['value'])->toBe(['Active User', 'Inactive User']);
    });
});

describe('enhanced date filtering with attribute detection', function () {
    it('flags attribute filtering for model attributes', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = DateFilter::make('Computed Date')->field('computed_date');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);

        $result = $filter->apply($query, '2024-01-01');

        expect($result)->toBe($query)
            ->and($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['type'])->toBe('date')
            ->and($details['range'])->toBeFalse();
    });

    it('handles date range filtering for attributes', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = DateFilter::make('Date Range')->field('computed_date')->range();

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Should NOT call where methods since it's an attribute

        $result = $filter->apply($query, ['from' => '2024-01-01', 'to' => '2024-01-31']);

        expect($filter->requiresAttributeFiltering())->toBeTrue();

        $details = $filter->getAttributeFilterDetails();
        expect($details['range'])->toBeTrue()
            ->and($details['value'])->toBe(['from' => '2024-01-01', 'to' => '2024-01-31']);
    });
});

describe('filter error prevention', function () {
    it('prevents SQL errors by detecting attributes before query execution', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn([]);

        $filter = TextFilter::make('Full Name')->field('full_name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Verify that where is NEVER called for attributes
        $query->shouldNotReceive('where');

        $filter->apply($query, 'test');

        expect($filter->requiresAttributeFiltering())->toBeTrue();
    });

    it('maintains SQL filtering performance for database columns', function () {
        $filter = TextFilter::make('Name')->field('name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('where')->once()->with('filtering_test_models.name', 'like', '%test%')->andReturnSelf();

        $filter->apply($query, 'test');

        expect($filter->requiresAttributeFiltering())->toBeFalse();
    });
});

describe('mixed filter combinations', function () {
    it('handles multiple filters with mixed database and attribute fields', function () {
        Schema::shouldReceive('connection')->with(null)->andReturnSelf();
        Schema::shouldReceive('getColumnListing')->with('filtering_test_models')->andReturn(['name', 'status']);

        $nameFilter = TextFilter::make('Name')->field('name'); // Database column
        $fullNameFilter = TextFilter::make('Full Name')->field('full_name'); // Attribute

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('where')->once()->andReturnSelf(); // Only for name filter

        // Apply database column filter - should use SQL
        $result1 = $nameFilter->apply($query, 'john');
        expect($nameFilter->requiresAttributeFiltering())->toBeFalse();

        // Apply attribute filter - should flag for PHP processing
        $result2 = $fullNameFilter->apply($query, 'john doe');
        expect($fullNameFilter->requiresAttributeFiltering())->toBeTrue();
    });
});
