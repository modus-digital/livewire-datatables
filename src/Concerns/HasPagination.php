<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait HasPagination
{
    use WithPagination;

    #[Url(as: 'per_page')]
    public int $perPage = 10;

    /**
     * Available per page options.
     */
    protected array $perPageOptions = [10, 25, 50, 100];

    /**
     * Get per page options.
     */
    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    /**
     * Update per page value.
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Set the per page value.
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    /**
     * Get the pagination view.
     */
    public function paginationView(): string
    {
        return 'livewire-datatables::partials.pagination';
    }

    /**
     * Get the simple pagination view.
     */
    public function paginationSimpleView(): string
    {
        return 'livewire-datatables::partials.pagination';
    }
}
