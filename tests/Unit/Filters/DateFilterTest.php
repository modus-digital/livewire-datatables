<?php

declare(strict_types=1);

use Carbon\Carbon;
use ModusDigital\LivewireDatatables\Filters\DateFilter;

class DFUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getSignedUpAtAttribute(): string
    {
        return (string) ($this->attributes['created_at'] ?? '');
    }

    public function company()
    {
        return $this->belongsTo(DFCompany::class, 'company_id');
    }
}

class DFCompany extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'companies';

    public function getEstablishedOnAttribute(): string
    {
        return (string) ($this->attributes['established_on'] ?? '');
    }
}

it('DateFilter single date op normale kolom', function () {
    $query = (new DFUser())->newQuery();
    $f = DateFilter::make('created_at');
    $f->apply($query, '2023-01-02');
    $sql = $query->toSql();
    // SQLite gebruikt strftime in whereDate
    expect($sql)->toContain("strftime('%Y-%m-%d', \"users\".\"created_at\") = cast(? as text)");
});

it('DateFilter range from/to op normale kolom', function () {
    $query = (new DFUser())->newQuery();
    $f = DateFilter::make('created_at')->range();
    $f->apply($query, ['from' => '2023-01-01', 'to' => '2023-01-31']);
    $sql = $query->toSql();
    expect($sql)->toContain('"users"."created_at" >= ?')
        ->and($sql)->toContain('"users"."created_at" <= ?');
});

it('DateFilter relatiepad met whereHas', function () {
    $query = (new DFUser())->newQuery();
    $f = DateFilter::make('company.joined_on');
    $f->apply($query, '2023-01-01');
    $sql = $query->toSql();
    expect($sql)->toContain('exists')
        ->and($sql)->toContain('from "companies"')
        ->and($sql)->toContain("strftime('%Y-%m-%d', \"joined_on\") = cast(? as text)");
});

it('DateFilter attribute-pad zet requiresAttributeFiltering en geen SQL where', function () {
    // direct attribute
    $query = (new DFUser())->newQuery();
    $f = DateFilter::make('signed_up_at');
    $f->apply($query, '2023-01-01');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('where');

    // relatie attribute
    $query = (new DFUser())->newQuery();
    $f = DateFilter::make('company.established_on');
    $f->apply($query, '2023-01-01');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('exists');
});
