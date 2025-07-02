<?php

use ModusDigital\LivewireDatatables\Actions\RowAction;
use ModusDigital\LivewireDatatables\Concerns\HasRowActions;

beforeEach(function () {
    $this->component = new class
    {
        use HasRowActions;

        public $actionExecuted = false;

        public $executedActionKey = null;

        public $recordId = null;

        protected function rowActions(): array
        {
            return [
                RowAction::make('edit', 'Edit')->callback(fn ($record, $component) => $component->editRecord($record)),
                RowAction::make('delete', 'Delete')->callback(fn ($record, $component) => $component->deleteRecord($record)),
            ];
        }

        public function getModel()
        {
            // Return a mock model that has a find method
            return new class
            {
                public function find($id)
                {
                    return (object) ['id' => $id, 'name' => 'Test Record'];
                }
            };
        }

        public function editRecord($record): void
        {
            $this->actionExecuted = true;
            $this->executedActionKey = 'edit';
            $this->recordId = $record->id;
        }

        public function deleteRecord($record): void
        {
            $this->actionExecuted = true;
            $this->executedActionKey = 'delete';
            $this->recordId = $record->id;
        }
    };
});

it('returns empty row actions by default', function () {
    $component = new class
    {
        use HasRowActions;
    };

    expect($component->getRowActions())->toBeEmpty();
});

it('returns defined row actions', function () {
    $actions = $this->component->getRowActions();

    expect($actions)->toHaveCount(2)
        ->and($actions->first())->toBeInstanceOf(RowAction::class)
        ->and($actions->first()->getLabel())->toBe('Edit')
        ->and($actions->last()->getLabel())->toBe('Delete');
});

it('caches row actions after first call', function () {
    $actions1 = $this->component->getRowActions();
    $actions2 = $this->component->getRowActions();

    expect($actions1)->toBe($actions2);
});

it('detects when row actions exist', function () {
    expect($this->component->hasRowActions())->toBeTrue();
});

it('detects when no row actions exist', function () {
    $component = new class
    {
        use HasRowActions;
    };

    expect($component->hasRowActions())->toBeFalse();
});

it('executes row action by key with record id', function () {
    expect($this->component->actionExecuted)->toBeFalse();

    $this->component->executeRowAction('edit', 123);

    expect($this->component->actionExecuted)->toBeTrue()
        ->and($this->component->executedActionKey)->toBe('edit')
        ->and($this->component->recordId)->toBe(123);
});

it('executes different row action by key', function () {
    $this->component->executeRowAction('delete', 456);

    expect($this->component->actionExecuted)->toBeTrue()
        ->and($this->component->executedActionKey)->toBe('delete')
        ->and($this->component->recordId)->toBe(456);
});

it('does nothing when executing non-existent row action', function () {
    $this->component->executeRowAction('non-existent', 123);

    expect($this->component->actionExecuted)->toBeFalse()
        ->and($this->component->executedActionKey)->toBeNull()
        ->and($this->component->recordId)->toBeNull();
});

it('handles row action without callback', function () {
    $component = new class
    {
        use HasRowActions;

        protected function rowActions(): array
        {
            return [
                RowAction::make('no_callback', 'No Callback'),
            ];
        }

        public function getModel()
        {
            return new class
            {
                public function find($id)
                {
                    return (object) ['id' => $id, 'name' => 'Test Record'];
                }
            };
        }
    };

    // Should not throw exception
    $component->executeRowAction('no_callback', 123);

    expect(true)->toBeTrue(); // Test passes if no exception thrown
});
