<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Concerns\HasSorting;

beforeEach(function () {
    $this->component = new class
    {
        use HasSorting;

        public $resetPageCalled = false;

        public function resetPage(): void
        {
            $this->resetPageCalled = true;
        }

        public function getModel(): Model
        {
            return new class extends Model
            {
                protected $table = 'test_table';
            };
        }

        public function isColumnSortable(string $field): bool
        {
            return in_array($field, ['name', 'email', 'created_at', 'user.name']);
        }

        public function getColumn(string $field): ?TextColumn
        {
            if ($field === 'custom_sort') {
                return TextColumn::make('Custom')->sortField('actual_field');
            }

            if ($field === 'user.name') {
                return TextColumn::make('User Name')->relationship('user.name');
            }

            return null;
        }
    };
});

it('has default sort field and direction', function () {
    expect($this->component->sortField)->toBe('')
        ->and($this->component->sortDirection)->toBe('asc');
});

it('sorts by field ascending when not currently sorted', function () {
    $this->component->sortBy('name');

    expect($this->component->sortField)->toBe('name')
        ->and($this->component->sortDirection)->toBe('asc')
        ->and($this->component->resetPageCalled)->toBeTrue();
});

it('toggles sort direction when already sorted by field', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'asc';

    $this->component->sortBy('name');

    expect($this->component->sortField)->toBe('name')
        ->and($this->component->sortDirection)->toBe('desc');
});

it('toggles back to ascending when sorted descending', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'desc';

    $this->component->sortBy('name');

    expect($this->component->sortField)->toBe('name')
        ->and($this->component->sortDirection)->toBe('asc');
});

it('does not sort by non-sortable field', function () {
    $originalField = $this->component->sortField;
    $originalDirection = $this->component->sortDirection;

    $this->component->sortBy('non_sortable');

    expect($this->component->sortField)->toBe($originalField)
        ->and($this->component->sortDirection)->toBe($originalDirection)
        ->and($this->component->resetPageCalled)->toBeFalse();
});

it('applies basic sorting to query', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'desc';

    $model = new class extends Model
    {
        protected $table = 'test_table';
    };
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldReceive('orderBy')->once()->with('test_table.name', 'desc')->andReturnSelf();

    $result = $this->component->applySorting($query);

    expect($result)->toBe($query);
});

it('applies default sorting when no sort field set', function () {
    $model = new class extends Model
    {
        protected $table = 'test_table';
    };
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldReceive('orderBy')->once()->with('test_table.id', 'asc')->andReturnSelf();

    $this->component->applySorting($query);
});

it('uses custom sort field from column', function () {
    $this->component->sortField = 'custom_sort';

    $model = new class extends Model
    {
        protected $table = 'test_table';
    };
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldReceive('orderBy')->once()->with('test_table.actual_field', 'asc')->andReturnSelf();

    $this->component->applySorting($query);
});

it('returns correct sort icon for unsorted field', function () {
    expect($this->component->getSortIcon('name'))->toBe('sort');
});

it('returns correct sort icon for ascending field', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'asc';

    expect($this->component->getSortIcon('name'))->toBe('sort-asc');
});

it('returns correct sort icon for descending field', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'desc';

    expect($this->component->getSortIcon('name'))->toBe('sort-desc');
});

it('detects when field is sorted', function () {
    $this->component->sortField = 'name';

    expect($this->component->isSorted('name'))->toBeTrue()
        ->and($this->component->isSorted('email'))->toBeFalse();
});

it('initializes default sorting when empty', function () {
    $this->component->sortField = '';

    $this->component->initializeSorting();

    expect($this->component->sortField)->toBe('id')
        ->and($this->component->sortDirection)->toBe('asc');
});

it('does not override existing sorting during initialization', function () {
    $this->component->sortField = 'name';
    $this->component->sortDirection = 'desc';

    $this->component->initializeSorting();

    expect($this->component->sortField)->toBe('name')
        ->and($this->component->sortDirection)->toBe('desc');
});

it('applies sorting for nested relationship paths', function () {
    $component = new class {
        use HasSorting;

        public function getModel(): Model
        {
            return new class extends Model {
                protected $table = 'posts';

                public function user()
                {
                    return $this->belongsTo(new class extends Model {
                        protected $table = 'users';

                        public function profile()
                        {
                            return $this->hasOne(new class extends Model {
                                protected $table = 'profiles';
                            }, 'user_id', 'id');
                        }
                    }, 'user_id');
                }
            };
        }

        public function isColumnSortable(string $field): bool
        {
            return $field === 'user.profile.name';
        }

        public function getColumn(string $field): ?TextColumn
        {
            return TextColumn::make('Profile Name')->relationship('user.profile.name');
        }
    };

    $component->sortField = 'user.profile.name';
    $model = $component->getModel();

    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('getModel')->andReturn($model)->byDefault();
    $query->shouldReceive('leftJoin')->once()->with('users as lwd_sort_0', 'posts.user_id', '=', 'lwd_sort_0.id')->andReturnSelf();
    $query->shouldReceive('leftJoin')->once()->with('profiles as lwd_sort_1', 'lwd_sort_0.id', '=', 'lwd_sort_1.user_id')->andReturnSelf();
    $query->shouldReceive('orderBy')->once()->with('lwd_sort_1.name', 'asc')->andReturnSelf();
    $query->shouldReceive('select')->once()->with('posts.*')->andReturnSelf();

    $result = $component->applySorting($query);

    expect($result)->toBe($query);
});
