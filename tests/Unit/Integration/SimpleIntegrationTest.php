<?php

use Illuminate\Support\Facades\Schema;
use ModusDigital\LivewireDatatables\Filters\TextFilter;
use ModusDigital\LivewireDatatables\Columns\TextColumn;
use ModusDigital\LivewireDatatables\Concerns\HasSorting;
use ModusDigital\LivewireDatatables\Concerns\HasFilters;
use ModusDigital\LivewireDatatables\Concerns\HasColumns;

// Simple test component
class SimpleTestComponent
{
    use HasSorting, HasFilters, HasColumns;

    public function getModel(): \Illuminate\Database\Eloquent\Model
    {
        return new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'test_table';
            protected $appends = ['full_name'];

            public function getFullNameAttribute(): string
            {
                return 'Test Name';
            }
        };
    }

    public function resetPage(): void {}

    protected function columns(): array
    {
        return [
            TextColumn::make('name')->sortable(),
            TextColumn::make('full_name')->field('full_name')->sortable(),
        ];
    }

    protected function filters(): array
    {
        return [
            TextFilter::make('Name')->field('name'),
            TextFilter::make('Full Name')->field('full_name'),
        ];
    }
}

beforeEach(function () {
    // Mock Schema
    Schema::shouldReceive('connection')->andReturnSelf();
    Schema::shouldReceive('getColumnListing')->with('test_table')->andReturn(['id', 'name', 'email']);

    $this->component = new SimpleTestComponent();
});

describe('simple integration tests', function () {
    it('detects attribute sorting correctly', function () {
        $this->component->sortField = 'full_name'; // This is an attribute

        // Create a mock query
        $query = \Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->component->getModel());

        // Apply sorting should detect attribute and flag it
        $this->component->applySorting($query);

        expect($this->component->requiresAttributeSorting())->toBeTrue();
    });

    it('does not flag attribute sorting for database columns', function () {
        $this->component->sortField = 'name'; // Database column

        $query = \Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->component->getModel());
        $query->shouldReceive('orderBy')->once()->andReturnSelf();

        $this->component->applySorting($query);

        expect($this->component->requiresAttributeSorting())->toBeFalse();
    });

    it('detects attribute filtering correctly', function () {
        $this->component->filters = ['full_name' => 'test'];

        expect($this->component->requiresAttributeFiltering())->toBeTrue();
    });

    it('does not flag attribute filtering for database columns', function () {
        $this->component->filters = ['name' => 'test'];

        expect($this->component->requiresAttributeFiltering())->toBeFalse();
    });

    it('handles mixed attribute and database field detection', function () {
        $this->component->sortField = 'full_name'; // Attribute
        $this->component->filters = ['name' => 'test']; // Database column

        $query = \Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $query->shouldReceive('getModel')->andReturn($this->component->getModel());

        $this->component->applySorting($query);

        // Should detect that attribute sorting is needed but not attribute filtering
        expect($this->component->requiresAttributeSorting())->toBeTrue();
        expect($this->component->requiresAttributeFiltering())->toBeFalse();
    });
});
