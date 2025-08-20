<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;
use ModusDigital\LivewireDatatables\Concerns\HasSorting;

// Test models
class SortingTestModel extends Model
{
    protected $table = 'sorting_test_models';

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function relatedModel()
    {
        return $this->belongsTo(RelatedSortingModel::class);
    }
}

class RelatedSortingModel extends Model
{
    protected $table = 'related_sorting_models';

    protected $appends = ['computed_value'];

    public function getComputedValueAttribute(): string
    {
        return 'computed_' . $this->name;
    }
}

// Test component
class SortingTestComponent
{
    use HasColumns, HasSorting;

    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function resetPage(): void
    {
        // Mock method
    }

    // Override to test different column configurations
    protected function columns(): array
    {
        return [
            TextColumn::make('name')->sortable(),
            TextColumn::make('full_name')->field('full_name')->sortable(), // Attribute
            TextColumn::make('related_name')->field('relatedModel.name')->sortable(), // Database relation
            TextColumn::make('related_computed')->field('relatedModel.computed_value')->sortable(), // Attribute relation
        ];
    }

    public function isColumnSortable(string $field): bool
    {
        return in_array($field, ['name', 'full_name', 'relatedModel.name', 'relatedModel.computed_value']);
    }

    // Make protected methods public for testing
    public function testApplySorting(Builder $query): Builder
    {
        return $this->applySorting($query);
    }

    public function testRequiresAttributeSorting(): bool
    {
        return $this->requiresAttributeSorting();
    }
}

beforeEach(function () {
    $this->testModel = new SortingTestModel;
    $this->component = new SortingTestComponent($this->testModel);
});

describe('enhanced sorting with attribute detection', function () {
    it('applies SQL sorting for regular database columns', function () {
        $this->component->sortField = 'name';
        $this->component->sortDirection = 'asc';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('orderBy')->once()->with('sorting_test_models.name', 'asc')->andReturnSelf();

        $result = $this->component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($this->component->testRequiresAttributeSorting())->toBeFalse();
    });

    it('flags attribute sorting for model attributes', function () {
        $this->component->sortField = 'full_name';
        $this->component->sortDirection = 'desc';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Should NOT call orderBy since it's an attribute

        $result = $this->component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($this->component->testRequiresAttributeSorting())->toBeTrue();
    });

    it('applies SQL sorting for relationship database columns', function () {
        $this->component->sortField = 'relatedModel.name';
        $this->component->sortDirection = 'asc';

        // Create a mock model with a mocked relationship
        $mockModel = Mockery::mock(SortingTestModel::class)->makePartial();
        $relatedModel = new RelatedSortingModel;
        $relationInstance = Mockery::mock(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        $relationInstance->shouldReceive('getRelated')->andReturn($relatedModel);
        $relationInstance->shouldReceive('getForeignKeyName')->andReturn('related_sorting_model_id');
        $relationInstance->shouldReceive('getOwnerKeyName')->andReturn('id');

        $mockModel->shouldReceive('relatedModel')->andReturn($relationInstance);

        $component = new SortingTestComponent($mockModel);
        $component->sortField = 'relatedModel.name';
        $component->sortDirection = 'asc';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($mockModel);
        $query->shouldReceive('leftJoin')->once()->andReturnSelf();
        $query->shouldReceive('orderBy')->once()->with('related_sorting_models.name', 'asc')->andReturnSelf();
        $query->shouldReceive('select')->once()->andReturnSelf();

        $result = $component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($component->testRequiresAttributeSorting())->toBeFalse();
    });

    it('flags attribute sorting for relationship attributes', function () {
        // Create a mock model with a mocked relationship
        $mockModel = Mockery::mock(SortingTestModel::class)->makePartial();
        $relatedModel = new RelatedSortingModel;
        $relationInstance = Mockery::mock(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        $relationInstance->shouldReceive('getRelated')->andReturn($relatedModel);

        $mockModel->shouldReceive('relatedModel')->andReturn($relationInstance);

        $component = new SortingTestComponent($mockModel);
        $component->sortField = 'relatedModel.computed_value';
        $component->sortDirection = 'desc';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($mockModel);
        // Should NOT call leftJoin or orderBy since it's an attribute

        $result = $component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($component->testRequiresAttributeSorting())->toBeTrue();
    });

    it('handles custom sort callbacks', function () {
        $customSortCalled = false;

        $column = TextColumn::make('Custom Field')
            ->sortUsing(function ($query, $direction) use (&$customSortCalled) {
                $customSortCalled = true;

                return $query->orderBy('special_field', $direction);
            });

        $component = new class($this->testModel, $column) extends SortingTestComponent
        {
            private $testColumn;

            public function __construct($model, $column)
            {
                parent::__construct($model);
                $this->testColumn = $column;
            }

            public function getColumn(string $field): ?TextColumn
            {
                if ($field === 'custom_field') {
                    return $this->testColumn;
                }

                return parent::getColumn($field);
            }

            public function isColumnSortable(string $field): bool
            {
                return $field === 'custom_field' || parent::isColumnSortable($field);
            }
        };

        $component->sortField = 'custom_field';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('orderBy')->once()->with('special_field', 'asc')->andReturnSelf();

        $result = $component->testApplySorting($query);

        expect($customSortCalled)->toBeTrue()
            ->and($result)->toBe($query)
            ->and($component->testRequiresAttributeSorting())->toBeFalse();
    });

    it('uses default sorting when no sort field is set', function () {
        // Override default to use created_at instead of id to avoid cast issues
        $component = new class($this->testModel) extends SortingTestComponent
        {
            protected string $defaultSortField = 'created_at';
        };

        // Empty sort field should use default
        $component->sortField = '';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('orderBy')->once()->with('sorting_test_models.created_at', 'asc')->andReturnSelf();

        $result = $component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($component->testRequiresAttributeSorting())->toBeFalse();
    });

    it('handles non-existent relationships gracefully', function () {
        $this->component->sortField = 'nonExistentRelation.field';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Should return unchanged query for non-existent relations

        $result = $this->component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($this->component->testRequiresAttributeSorting())->toBeFalse();
    });
});

describe('sorting error prevention', function () {
    it('prevents SQL errors by detecting attributes before query execution', function () {
        // This test ensures that we never try to execute SQL sorting on attributes
        $this->component->sortField = 'full_name'; // This is an attribute

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        // Verify that orderBy is NEVER called for attributes
        $query->shouldNotReceive('orderBy');

        $result = $this->component->testApplySorting($query);

        expect($this->component->testRequiresAttributeSorting())->toBeTrue();
    });

    it('maintains SQL sorting performance for database columns', function () {
        // This test ensures that regular database columns still use efficient SQL sorting
        $this->component->sortField = 'name'; // This is a database column

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('orderBy')->once()->with('sorting_test_models.name', 'asc')->andReturnSelf();

        $result = $this->component->testApplySorting($query);

        expect($this->component->testRequiresAttributeSorting())->toBeFalse();
    });
});

describe('column integration', function () {
    it('uses custom sort field from column configuration', function () {
        $component = new class($this->testModel) extends SortingTestComponent
        {
            public function getColumn(string $field): ?TextColumn
            {
                if ($field === 'custom_sort') {
                    return TextColumn::make('Custom')->field('custom_sort')->sortField('actual_database_field');
                }

                return parent::getColumn($field);
            }

            public function isColumnSortable(string $field): bool
            {
                return $field === 'custom_sort' || parent::isColumnSortable($field);
            }
        };

        $component->sortField = 'custom_sort';

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->testModel);
        $query->shouldReceive('orderBy')->once()->with('sorting_test_models.actual_database_field', 'asc')->andReturnSelf();

        $result = $component->testApplySorting($query);

        expect($result)->toBe($query)
            ->and($component->testRequiresAttributeSorting())->toBeFalse();
    });
});
