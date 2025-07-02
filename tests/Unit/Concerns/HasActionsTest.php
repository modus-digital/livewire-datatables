<?php

use ModusDigital\LivewireDatatables\Actions\Action;
use ModusDigital\LivewireDatatables\Concerns\HasActions;

beforeEach(function () {
    $this->component = new class
    {
        use HasActions;

        public $actionExecuted = false;

        public $executedActionKey = null;

        protected function actions(): array
        {
            return [
                Action::make('export', 'Export')
                    ->callback(function ($component) {
                        $component->actionExecuted = true;
                        $component->executedActionKey = 'export';
                    }),
                Action::make('import', 'Import')
                    ->callback(function ($component) {
                        $component->actionExecuted = true;
                        $component->executedActionKey = 'import';
                    }),
            ];
        }
    };
});

it('returns empty actions by default', function () {
    $component = new class
    {
        use HasActions;
    };

    expect($component->getActions())->toBeEmpty();
});

it('returns defined actions', function () {
    $actions = $this->component->getActions();

    expect($actions)->toHaveCount(2)
        ->and($actions->first())->toBeInstanceOf(Action::class)
        ->and($actions->first()->getName())->toBe('Export')
        ->and($actions->last()->getName())->toBe('Import');
});

it('caches actions after first call', function () {
    // First call
    $actions1 = $this->component->getActions();

    // Second call should return cached version
    $actions2 = $this->component->getActions();

    expect($actions1)->toBe($actions2);
});

it('detects when actions exist', function () {
    expect($this->component->hasActions())->toBeTrue();
});

it('detects when no actions exist', function () {
    $component = new class
    {
        use HasActions;
    };

    expect($component->hasActions())->toBeFalse();
});

it('executes action by key', function () {
    expect($this->component->actionExecuted)->toBeFalse();

    $this->component->executeAction('export');

    expect($this->component->actionExecuted)->toBeTrue()
        ->and($this->component->executedActionKey)->toBe('export');
});

it('executes different action by key', function () {
    $this->component->executeAction('import');

    expect($this->component->actionExecuted)->toBeTrue()
        ->and($this->component->executedActionKey)->toBe('import');
});

it('does nothing when executing non-existent action', function () {
    $this->component->executeAction('non-existent');

    expect($this->component->actionExecuted)->toBeFalse()
        ->and($this->component->executedActionKey)->toBeNull();
});

it('handles action without callback', function () {
    $component = new class
    {
        use HasActions;

        protected function actions(): array
        {
            return [
                Action::make('no_callback', 'No Callback'),
            ];
        }
    };

    // Should not throw exception
    $component->executeAction('no_callback');

    expect(true)->toBeTrue(); // Test passes if no exception thrown
});
