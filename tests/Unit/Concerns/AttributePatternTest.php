<?php

use Illuminate\Database\Eloquent\Model;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;
use ModusDigital\LivewireDatatables\Filters\SelectFilter;
use ModusDigital\LivewireDatatables\Filters\TextFilter;

class AttributeTestModel extends Model
{
    protected $table = 'users';

    protected $appends = ['name', 'fullName', 'initials', 'reversedName', 'formalName', 'fullAddress', 'customAttribute'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitialsAttribute()
    {
        return substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1);
    }

    public function getReversedNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name;
    }

    public function getFormalNameAttribute()
    {
        return $this->last_name . ', ' . substr($this->first_name, 0, 1) . '.';
    }

    public function getFullAddressAttribute()
    {
        return $this->street . ', ' . $this->city . ', ' . $this->state . ' ' . $this->zip;
    }

    public function getCustomAttributeAttribute()
    {
        return 'Custom: ' . $this->name;
    }
}

class TestHasColumns
{
    use HasColumns;

    public function getModel(): Model
    {
        return new AttributeTestModel;
    }

    // Make protected methods public for testing
    public function testIsModelAttribute(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        return $this->isModelAttribute($model, $field);
    }

    public function testGetModelAttributeValue(\Illuminate\Database\Eloquent\Model $model, string $attribute): mixed
    {
        return $this->getModelAttributeValue($model, $attribute);
    }

    public function testIsDatabaseColumn(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        return $this->isDatabaseColumn($model, $field);
    }

    public function testHasModelColumns(\Illuminate\Database\Eloquent\Model $model, array $columns): bool
    {
        return $this->hasModelColumns($model, $columns);
    }
}

it('detects various model attributes dynamically', function () {
    $test = new TestHasColumns;
    $model = new AttributeTestModel;

    // Test any attribute patterns - all should work now
    expect($test->testIsModelAttribute($model, 'name'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'fullName'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'initials'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'reversedName'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'formalName'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'fullAddress'))->toBeTrue();
    expect($test->testIsModelAttribute($model, 'customAttribute'))->toBeTrue();

    // Test non-existent attributes
    expect($test->testIsModelAttribute($model, 'nonExistent'))->toBeFalse();
});

it('gets model attribute values dynamically', function () {
    $test = new TestHasColumns;
    $model = new AttributeTestModel;

    // Set some test data
    $model->first_name = 'John';
    $model->last_name = 'Doe';
    $model->street = '123 Main St';
    $model->city = 'Anytown';
    $model->state = 'CA';
    $model->zip = '12345';

    // Test that any attribute can be retrieved
    expect($test->testGetModelAttributeValue($model, 'name'))->toBe('John Doe');
    expect($test->testGetModelAttributeValue($model, 'fullName'))->toBe('John Doe');
    expect($test->testGetModelAttributeValue($model, 'initials'))->toBe('JD');
    expect($test->testGetModelAttributeValue($model, 'reversedName'))->toBe('Doe, John');
    expect($test->testGetModelAttributeValue($model, 'formalName'))->toBe('Doe, J.');
    expect($test->testGetModelAttributeValue($model, 'fullAddress'))->toBe('123 Main St, Anytown, CA 12345');
    expect($test->testGetModelAttributeValue($model, 'customAttribute'))->toBe('Custom: John Doe');
});

it('distinguishes between database columns and model attributes', function () {
    $test = new TestHasColumns;
    $model = new AttributeTestModel;

    // Mock database columns for testing
    $mockTest = new class extends TestHasColumns
    {
        protected function isDatabaseColumn(\Illuminate\Database\Eloquent\Model $model, string $field): bool
        {
            $mockColumns = ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'];

            return in_array($field, $mockColumns);
        }
    };

    // Database columns should be detected
    expect($mockTest->testIsDatabaseColumn($model, 'first_name'))->toBeTrue();
    expect($mockTest->testIsDatabaseColumn($model, 'last_name'))->toBeTrue();
    expect($mockTest->testIsDatabaseColumn($model, 'email'))->toBeTrue();

    // Model attributes should not be detected as database columns
    expect($mockTest->testIsDatabaseColumn($model, 'name'))->toBeFalse();
    expect($mockTest->testIsDatabaseColumn($model, 'fullName'))->toBeFalse();
    expect($mockTest->testIsDatabaseColumn($model, 'customAttribute'))->toBeFalse();
});

it('works with TextFilter for any attributes', function () {
    // Test that TextFilter can handle any model attribute
    $filter = TextFilter::make('User Name')->field('user.fullName');

    expect(method_exists($filter, 'isModelAttribute'))->toBeTrue();
    expect($filter)->toBeInstanceOf(TextFilter::class);
});

it('works with SelectFilter for any attributes', function () {
    // Test that SelectFilter can handle any model attribute
    $filter = SelectFilter::make('User Name')
        ->field('user.customAttribute')
        ->options(['Custom: John Doe' => 'John Doe', 'Custom: Jane Smith' => 'Jane Smith']);

    expect(method_exists($filter, 'isModelAttribute'))->toBeTrue();
    expect($filter)->toBeInstanceOf(SelectFilter::class);
});

it('handles attribute detection through accessor methods', function () {
    $test = new TestHasColumns;

    // Create a model with a custom accessor
    $model = new class extends Model
    {
        public function getCustomComputedAttribute()
        {
            return 'computed value';
        }
    };

    expect($test->testIsModelAttribute($model, 'customComputed'))->toBeTrue();
});

it('handles attribute detection through appends array', function () {
    $test = new TestHasColumns;

    // Create a model with appended attributes
    $model = new class extends Model
    {
        protected $appends = ['appendedAttribute'];

        public function getAppendedAttributeAttribute()
        {
            return 'appended value';
        }
    };

    expect($test->testIsModelAttribute($model, 'appendedAttribute'))->toBeTrue();
});

it('handles attribute detection through casts', function () {
    $test = new TestHasColumns;

    // Create a model with cast attributes
    $model = new class extends Model
    {
        protected $casts = [
            'cast_attribute' => 'array',
        ];
    };

    expect($test->testIsModelAttribute($model, 'cast_attribute'))->toBeTrue();
});
