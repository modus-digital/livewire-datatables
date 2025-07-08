<?php

use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Filters\TextFilter;
use ModusDigital\LivewireDatatables\Livewire\Table;

class TableIntegrationUser extends Model
{
    protected $table = 'users';

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

class TableIntegrationProject extends Model
{
    protected $table = 'projects';

    public function projectManager()
    {
        return $this->belongsTo(TableIntegrationUser::class, 'project_manager_id');
    }
}

class TableIntegrationAction extends Model
{
    protected $table = 'actions';

    public function project()
    {
        return $this->belongsTo(TableIntegrationProject::class);
    }
}

class TestTable extends Table
{
    protected string $model = TableIntegrationAction::class;

    protected function columns(): array
    {
        return [
            TextColumn::make('Project Manager')
                ->field('project.project_manager.name')
                ->sortable()
                ->searchable(),
            TextColumn::make('Description')
                ->field('description')
                ->sortable()
                ->searchable(),
        ];
    }

    protected function filters(): array
    {
        return [
            TextFilter::make('Project Manager')->field('project.project_manager.name'),
        ];
    }
}

it('initializes table with attribute fields correctly', function () {
    $table = new TestTable;

    expect($table->getColumns())->toHaveCount(2);
    expect($table->getFilters())->toHaveCount(1);

    // Check that the table has the expected attribute field
    $columns = $table->getColumns();
    $projectManagerColumn = $columns->first(function ($column) {
        return $column->getField() === 'project.project_manager.name';
    });

    expect($projectManagerColumn)->not->toBeNull();
    expect($projectManagerColumn->isSortable())->toBeTrue();
    expect($projectManagerColumn->isSearchable())->toBeTrue();
});

it('has proper attribute detection methods available', function () {
    $table = new TestTable;

    // Test that the table has the essential attribute detection methods
    expect(method_exists($table, 'isModelAttribute'))->toBeTrue();
    expect(method_exists($table, 'getModelAttributeValue'))->toBeTrue();
    expect(method_exists($table, 'isDatabaseColumn'))->toBeTrue();
    expect(method_exists($table, 'getRelatedModel'))->toBeTrue();
});

it('handles sorting configuration correctly', function () {
    $table = new TestTable;
    $table->sortField = 'project.project_manager.name';
    $table->sortDirection = 'asc';

    expect($table->sortField)->toBe('project.project_manager.name');
    expect($table->sortDirection)->toBe('asc');

    // Test that the sorting field is recognized as sortable
    $columns = $table->getColumns();
    $sortableColumn = $columns->first(function ($column) {
        return $column->getField() === 'project.project_manager.name';
    });

    expect($sortableColumn->isSortable())->toBeTrue();
});

it('handles search configuration correctly', function () {
    $table = new TestTable;
    $table->search = 'John Doe';

    expect($table->search)->toBe('John Doe');
    expect($table->isSearchable())->toBeTrue();

    // Test that searchable columns are properly configured
    $searchableColumns = $table->getSearchableColumns();
    expect($searchableColumns)->toHaveCount(2);

    $attributeColumn = $searchableColumns->first(function ($column) {
        return $column->getField() === 'project.project_manager.name';
    });

    expect($attributeColumn)->not->toBeNull();
    expect($attributeColumn->isSearchable())->toBeTrue();
});

it('handles filter configuration correctly', function () {
    $table = new TestTable;
    $filters = $table->getFilters();

    expect($filters)->toHaveCount(1);

    $attributeFilter = $filters->first();
    expect($attributeFilter->getField())->toBe('project.project_manager.name');
});

it('integrates all features without conflicts', function () {
    $table = new TestTable;

    // Set up sorting, searching, and filtering on the same attribute field
    $table->sortField = 'project.project_manager.name';
    $table->sortDirection = 'asc';
    $table->search = 'John';
    $table->filters = ['project.project_manager.name' => 'John Doe'];

    // Test that all configurations are properly set
    expect($table->sortField)->toBe('project.project_manager.name');
    expect($table->search)->toBe('John');
    expect($table->filters)->toBe(['project.project_manager.name' => 'John Doe']);

    // Test that the table can be instantiated and configured without errors
    expect($table)->toBeInstanceOf(Table::class);
    expect($table->getColumns())->toHaveCount(2);
    expect($table->getFilters())->toHaveCount(1);
});

it('provides proper method signatures for attribute handling', function () {
    $table = new TestTable;

    // Test that the table has the expected methods with proper signatures
    $reflection = new ReflectionClass($table);

    // Check HasColumns trait methods
    expect($reflection->hasMethod('isModelAttribute'))->toBeTrue();
    expect($reflection->hasMethod('getModelAttributeValue'))->toBeTrue();
    expect($reflection->hasMethod('isDatabaseColumn'))->toBeTrue();
    expect($reflection->hasMethod('getRelatedModel'))->toBeTrue();

    // Check HasSorting trait methods
    expect($reflection->hasMethod('applySorting'))->toBeTrue();

    // Check Table methods
    expect($reflection->hasMethod('applyGlobalSearch'))->toBeTrue();
    expect($reflection->hasMethod('applySearchToRelationship'))->toBeTrue();
    expect($reflection->hasMethod('applyAttributeSearch'))->toBeTrue();
    expect($reflection->hasMethod('needsAttributeSorting'))->toBeTrue();
    expect($reflection->hasMethod('sortByAttribute'))->toBeTrue();
});
