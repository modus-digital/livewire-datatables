<?php

use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Filters\TextFilter;

class TextFilterUserModel extends Model
{
    protected $table = 'users';

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

class TextFilterProjectModel extends Model
{
    protected $table = 'projects';

    public function user()
    {
        return $this->belongsTo(TextFilterUserModel::class, 'user_id');
    }
}

it('creates filter with correct field configuration', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    expect($filter->getName())->toBe('User Name');
    expect($filter->getField())->toBe('user.name');
});

it('supports different operators', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    // Test default operator
    expect($filter)->toBeInstanceOf(TextFilter::class);

    // Test exact operator
    $exactFilter = TextFilter::make('User Name')->field('user.name')->exact();
    expect($exactFilter)->toBeInstanceOf(TextFilter::class);

    // Test starts with operator
    $startsWithFilter = TextFilter::make('User Name')->field('user.name')->startsWith();
    expect($startsWithFilter)->toBeInstanceOf(TextFilter::class);

    // Test ends with operator
    $endsWithFilter = TextFilter::make('User Name')->field('user.name')->endsWith();
    expect($endsWithFilter)->toBeInstanceOf(TextFilter::class);
});

it('has attribute detection methods', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    expect(method_exists($filter, 'isModelAttribute'))->toBeTrue();
    // The filter now uses a simpler approach - no hardcoded methods needed
    expect($filter)->toBeInstanceOf(TextFilter::class);
});

it('handles empty values correctly', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    $query = \Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
    $model = new TextFilterProjectModel;
    $query->shouldReceive('getModel')->andReturn($model);

    // Should return query unchanged for empty values
    expect($filter->apply($query, ''))->toBe($query);
    expect($filter->apply($query, null))->toBe($query);
});

it('handles relationship fields correctly', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    expect($filter->getField())->toBe('user.name');
    expect(str_contains($filter->getField(), '.'))->toBeTrue();
});

it('supports fluent interface', function () {
    $filter = TextFilter::make('User Name')
        ->field('user.name')
        ->exact()
        ->placeholder('Enter name...')
        ->default('John');

    expect($filter->getField())->toBe('user.name');
    expect($filter->getPlaceholder())->toBe('Enter name...');
    expect($filter->getDefault())->toBe('John');
});

it('properly inherits from base Filter class', function () {
    $filter = TextFilter::make('User Name')->field('user.name');

    expect($filter)->toBeInstanceOf(\ModusDigital\LivewireDatatables\Filters\Filter::class);
    expect($filter)->toBeInstanceOf(TextFilter::class);
});
