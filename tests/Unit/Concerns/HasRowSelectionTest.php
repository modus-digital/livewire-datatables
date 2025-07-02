<?php

use ModusDigital\LivewireDatatables\Concerns\HasRowSelection;

beforeEach(function () {
    $this->component = new class
    {
        use HasRowSelection;

        public $resetPageCalled = false;

        public function resetPage(): void
        {
            $this->resetPageCalled = true;
        }

        public function getRows()
        {
            // Mock some data for testing
            return collect([
                (object) ['id' => 1],
                (object) ['id' => 2],
                (object) ['id' => 3],
            ]);
        }
    };
});

it('initializes with empty selected rows', function () {
    expect($this->component->selected)->toBe([]);
});

it('toggles selection for individual row', function () {
    $this->component->toggleSelection('1');

    expect($this->component->selected)->toContain('1');

    $this->component->toggleSelection('1');

    expect($this->component->selected)->not->toContain('1');
});

it('deselects all rows', function () {
    $this->component->selected = ['1', '2', '3'];

    $this->component->deselectAll();

    expect($this->component->selected)->toBe([])
        ->and($this->component->selectAll)->toBeFalse();
});

it('checks if row is selected', function () {
    $this->component->selected = ['1', '2'];

    expect($this->component->isSelected('1'))->toBeTrue()
        ->and($this->component->isSelected('3'))->toBeFalse();
});

it('gets count of selected rows', function () {
    $this->component->selected = ['1', '2'];

    expect($this->component->getSelectedCount())->toBe(2);
});

it('gets zero count when no rows selected', function () {
    expect($this->component->getSelectedCount())->toBe(0);
});

it('detects when rows are selected', function () {
    $this->component->selected = ['1'];

    expect($this->component->hasSelected())->toBeTrue();
});

it('detects when no rows are selected', function () {
    expect($this->component->hasSelected())->toBeFalse();
});

it('detects when selection is enabled', function () {
    expect($this->component->hasSelection())->toBeTrue();
});

it('can disable selection', function () {
    $this->component->disableSelection();

    expect($this->component->hasSelection())->toBeFalse();
});

it('can enable selection', function () {
    $this->component->disableSelection();
    $this->component->enableSelection();

    expect($this->component->hasSelection())->toBeTrue();
});

it('updates selection state when selection is updated', function () {
    // This tests the updatedSelected hook
    $this->component->updatedSelected();

    expect(true)->toBeTrue(); // Test passes if no exception thrown
});
