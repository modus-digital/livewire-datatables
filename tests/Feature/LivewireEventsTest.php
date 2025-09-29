<?php

declare(strict_types=1);

use Livewire\Livewire;
use ModusDigital\LivewireDatatables\Livewire\Table;

class EventUserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
}

class EventTable extends Table
{
    protected string $model = EventUserModel::class;

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('tests::blank');
    }
}

it('dispatcht events bij resetFilters en resetFilter', function () {
    Livewire::test(EventTable::class)
        ->call('resetFilters')
        ->assertDispatched('filters-cleared')
        ->call('resetFilter', 'status')
        ->assertDispatched('filter-cleared');
});
