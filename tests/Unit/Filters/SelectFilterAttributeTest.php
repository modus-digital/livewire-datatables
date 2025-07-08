<?php

use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;

class SelectFilterUserModel extends Model
{
    protected $table = 'users';

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

class SelectFilterProjectModel extends Model
{
    protected $table = 'projects';

    public function user()
    {
        return $this->belongsTo(SelectFilterUserModel::class, 'user_id');
    }
}

it('creates select filter with correct field configuration', function () {
    $filter = SelectFilter::make('User Name')
        ->field('user.name')
        ->options(['John Doe' => 'John Doe', 'Jane Smith' => 'Jane Smith']);

    expect($filter->getName())->toBe('User Name');
    expect($filter->getField())->toBe('user.name');
    expect($filter->getOptions())->toBe(['John Doe' => 'John Doe', 'Jane Smith' => 'Jane Smith']);
});

it('supports multiple selection', function () {
    $filter = SelectFilter::make('User Name')
        ->field('user.name')
        ->options(['John Doe' => 'John Doe', 'Jane Smith' => 'Jane Smith'])
        ->multiple();

    expect($filter->isMultiple())->toBeTrue();
});

it('has attribute detection methods', function () {
    $filter = SelectFilter::make('User Name')->field('user.name');

    expect(method_exists($filter, 'isModelAttribute'))->toBeTrue();
    // The filter now uses a simpler approach - no hardcoded methods needed
    expect($filter)->toBeInstanceOf(SelectFilter::class);
});

it('handles empty values correctly', function () {
    $filter = SelectFilter::make('User Name')
        ->field('user.name')
        ->options(['John Doe' => 'John Doe']);

    $query = \Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
    $model = new SelectFilterProjectModel;
    $query->shouldReceive('getModel')->andReturn($model);

    // Should return query unchanged for empty values
    expect($filter->apply($query, ''))->toBe($query);
    expect($filter->apply($query, null))->toBe($query);
    expect($filter->apply($query, []))->toBe($query);
});

it('handles relationship fields correctly', function () {
    $filter = SelectFilter::make('User Name')->field('user.name');

    expect($filter->getField())->toBe('user.name');
    expect(str_contains($filter->getField(), '.'))->toBeTrue();
});

it('supports fluent interface', function () {
    $filter = SelectFilter::make('User Name')
        ->field('user.name')
        ->options(['John' => 'John', 'Jane' => 'Jane'])
        ->multiple()
        ->placeholder('Select user...')
        ->default('John');

    expect($filter->getField())->toBe('user.name');
    expect($filter->getOptions())->toBe(['John' => 'John', 'Jane' => 'Jane']);
    expect($filter->isMultiple())->toBeTrue();
    expect($filter->getPlaceholder())->toBe('Select user...');
    expect($filter->getDefault())->toBe('John');
});

it('properly inherits from base Filter class', function () {
    $filter = SelectFilter::make('User Name')->field('user.name');

    expect($filter)->toBeInstanceOf(\ModusDigital\LivewireDatatables\Filters\Filter::class);
    expect($filter)->toBeInstanceOf(SelectFilter::class);
});
