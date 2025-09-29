<?php

declare(strict_types=1);

use ModusDigital\LivewireDatatables\Filters\TextFilter;

class TFUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function account()
    {
        return $this->belongsTo(TFAccount::class, 'account_id');
    }
}

class TFAccount extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'accounts';

    public function getSlugAttribute(): string
    {
        return strtolower((string) ($this->attributes['name'] ?? ''));
    }
}

it('TextFilter exact/contains/startsWith/endsWith SQL op normale kolom', function () {
    $query = (new TFUser)->newQuery();

    // contains
    $f = TextFilter::make('first_name')->contains();
    $f->apply($query, 'John');
    $sql = $query->toSql();
    $bindings = $query->getQuery()->getBindings();
    expect($sql)->toContain('where "users"."first_name" like ?')
        ->and($bindings[count($bindings) - 1])->toBe('%John%');

    // startsWith
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('first_name')->startsWith();
    $f->apply($query, 'Jo');
    $sql = $query->toSql();
    $bindings = $query->getQuery()->getBindings();
    expect($sql)->toContain('where "users"."first_name" like ?')
        ->and($bindings[count($bindings) - 1])->toBe('Jo%');

    // endsWith
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('first_name')->endsWith();
    $f->apply($query, 'hn');
    $sql = $query->toSql();
    $bindings = $query->getQuery()->getBindings();
    expect($sql)->toContain('where "users"."first_name" like ?')
        ->and($bindings[count($bindings) - 1])->toBe('%hn');

    // exact
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('first_name')->exact();
    $f->apply($query, 'John');
    $sql = $query->toSql();
    $bindings = $query->getQuery()->getBindings();
    expect($sql)->toContain('where "users"."first_name" = ?')
        ->and($bindings[count($bindings) - 1])->toBe('John');
});

it('TextFilter relatiepad met whereHas', function () {
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('account.name')->contains();
    $f->apply($query, 'Acme');
    $sql = $query->toSql();
    expect($sql)->toContain('exists')
        ->and($sql)->toContain('from "accounts"')
        ->and($sql)->toContain('"name" like ?');
});

it('TextFilter attribute-pad zet requiresAttributeFiltering en geen SQL where', function () {
    // direct attribute
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('display_name')->contains();
    $f->apply($query, 'Jane');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('where');

    // relatie attribute
    $query = (new TFUser)->newQuery();
    $f = TextFilter::make('account.slug')->contains();
    $f->apply($query, 'acme');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('exists');
});
