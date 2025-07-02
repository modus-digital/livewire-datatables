<?php

use ModusDigital\LivewireDatatables\Actions\Action;

it('creates action with key and label', function () {
    $action = Action::make('export', 'Export Data');

    expect($action->getKey())->toBe('export')
        ->and($action->getLabel())->toBe('Export Data');
});

it('sets custom label', function () {
    $action = Action::make('export', 'Export')->label('Export All Data');

    expect($action->getLabel())->toBe('Export All Data');
});

it('sets icon', function () {
    $action = Action::make('export', 'Export')->icon('download');

    expect($action->getIcon())->toBe('download');
});

it('has null icon by default', function () {
    $action = Action::make('export', 'Export');

    expect($action->getIcon())->toBeNull();
});

it('sets custom class', function () {
    $action = Action::make('export', 'Export')->class('btn btn-primary');

    expect($action->getClass())->toBe('btn btn-primary');
});

it('has default class', function () {
    $action = Action::make('export', 'Export');

    expect($action->getClass())->toBe('');
});

it('sets confirm message', function () {
    $action = Action::make('delete', 'Delete')->confirm('Are you sure?');

    expect($action->getConfirmMessage())->toBe('Are you sure?');
});

it('has null confirm message by default', function () {
    $action = Action::make('export', 'Export');

    expect($action->getConfirmMessage())->toBeNull();
});

it('sets visibility condition as boolean', function () {
    $action = Action::make('export', 'Export')->visible(false);

    expect($action->isVisible())->toBeFalse();
});

it('is visible by default', function () {
    $action = Action::make('export', 'Export');

    expect($action->isVisible())->toBeTrue();
});

it('sets visibility condition as closure', function () {
    $action = Action::make('export', 'Export')->visible(fn($record) => $record === 'admin');

    expect($action->isVisible('admin'))->toBeTrue()
        ->and($action->isVisible('user'))->toBeFalse();
});

it('sets callback', function () {
    $executed = false;
    $action = Action::make('export', 'Export')->callback(function () use (&$executed) {
        $executed = true;
    });

    $callback = $action->getCallback();
    $callback();

    expect($executed)->toBeTrue();
});

it('has null callback by default', function () {
    $action = Action::make('export', 'Export');

    expect($action->getCallback())->toBeNull();
});

it('chains methods fluently', function () {
    $action = Action::make('export', 'Export')
        ->label('Export All')
        ->icon('download')
        ->class('btn-primary')
        ->confirm('Export data?')
        ->visible(true)
        ->callback(fn() => null);

    expect($action->getLabel())->toBe('Export All')
        ->and($action->getIcon())->toBe('download')
        ->and($action->getClass())->toBe('btn-primary')
        ->and($action->getConfirmMessage())->toBe('Export data?')
        ->and($action->isVisible())->toBeTrue()
        ->and($action->getCallback())->toBeInstanceOf(Closure::class);
});
