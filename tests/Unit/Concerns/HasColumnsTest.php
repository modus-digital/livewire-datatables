<?php

use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Columns\IconColumn;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;

beforeEach(function () {
    $this->component = new class
    {
        use HasColumns;

        protected function columns(): array
        {
            return [
                TextColumn::make('Name')->searchable()->sortable(),
                TextColumn::make('Email')->searchable(),
                IconColumn::make('Status')->sortable(),
                TextColumn::make('Created At')->field('created_at'),
            ];
        }
    };
});

it('returns empty columns by default', function () {
    $component = new class
    {
        use HasColumns;
    };

    expect($component->getColumns())->toBeEmpty();
});

it('returns defined columns', function () {
    $columns = $this->component->getColumns();

    expect($columns)->toHaveCount(4)
        ->and($columns->first())->toBeInstanceOf(TextColumn::class)
        ->and($columns->first()->getName())->toBe('Name')
        ->and($columns->get(2))->toBeInstanceOf(IconColumn::class);
});

it('caches columns after first call', function () {
    $columns1 = $this->component->getColumns();
    $columns2 = $this->component->getColumns();

    expect($columns1)->toBe($columns2);
});

it('gets searchable columns only', function () {
    $searchableColumns = $this->component->getSearchableColumns();

    expect($searchableColumns)->toHaveCount(2)
        ->and($searchableColumns->first()->getName())->toBe('Name')
        ->and($searchableColumns->last()->getName())->toBe('Email');
});

it('gets sortable columns only', function () {
    $sortableColumns = $this->component->getSortableColumns();

    expect($sortableColumns)->toHaveCount(2)
        ->and($sortableColumns->first()->getName())->toBe('Name')
        ->and($sortableColumns->last()->getName())->toBe('Status');
});

it('finds column by field', function () {
    $column = $this->component->getColumn('name');

    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('Name');
});

it('finds column by custom field', function () {
    $column = TextColumn::make('Custom')->field('custom_field');

    $component = new class([$column])
    {
        use HasColumns;

        public function __construct(private array $testColumns) {}

        protected function columns(): array
        {
            return $this->testColumns;
        }

        public function getModel(): Model
        {
            return new class extends Model
            {
                protected $table = 'test_table';
            };
        }
    };

    expect($component->getColumn('custom_field'))->toBe($column)
        ->and($component->getColumn('non_existent'))->toBeNull();
});

it('detects model attributes correctly', function () {
    $model = new class extends Model
    {
        protected $appends = ['full_name'];

        protected $casts = ['settings' => 'array'];

        public function getFullNameAttribute()
        {
            return $this->first_name . ' ' . $this->last_name;
        }
    };

    $component = new class
    {
        use HasColumns;

        public function getModel(): Model
        {
            return new class extends Model
            {
                protected $table = 'test_table';
            };
        }

        // Expose the protected method for testing
        public function testIsModelAttribute($model, $field)
        {
            return $this->isModelAttribute($model, $field);
        }
    };

    expect($component->testIsModelAttribute($model, 'full_name'))->toBeTrue()
        ->and($component->testIsModelAttribute($model, 'settings'))->toBeTrue()
        ->and($component->testIsModelAttribute($model, 'regular_field'))->toBeFalse();
});

it('returns null for non-existent column', function () {
    $column = $this->component->getColumn('non_existent');

    expect($column)->toBeNull();
});

it('checks if column is sortable', function () {
    expect($this->component->isColumnSortable('name'))->toBeTrue()
        ->and($this->component->isColumnSortable('email'))->toBeFalse()
        ->and($this->component->isColumnSortable('status'))->toBeTrue()
        ->and($this->component->isColumnSortable('non_existent'))->toBeFalse();
});

it('detects when columns exist', function () {
    expect($this->component->hasColumns())->toBeTrue();
});

it('detects when no columns exist', function () {
    $component = new class
    {
        use HasColumns;
    };

    expect($component->hasColumns())->toBeFalse();
});
