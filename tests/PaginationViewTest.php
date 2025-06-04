<?php

use ModusDigital\LivewireDatatables\Concerns\HasPagination;

it('uses datatable pagination views', function () {
    $table = new class {
        use HasPagination;
    };

    expect($table->paginationView())->toBe('livewire-datatables::partials.pagination')
        ->and($table->paginationSimpleView())->toBe('livewire-datatables::partials.pagination');
});
