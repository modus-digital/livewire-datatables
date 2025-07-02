<?php

use ModusDigital\LivewireDatatables\Actions\RowAction;

it('builds row action fluently', function () {
    $action = RowAction::make('edit', 'Edit')
        ->class('bg-blue-500')
        ->confirm('Sure?');

    expect($action->getKey())->toBe('edit')
        ->and($action->getLabel())->toBe('Edit')
        ->and($action->getClass())->toBe('bg-blue-500')
        ->and($action->getConfirmMessage())->toBe('Sure?');
});
