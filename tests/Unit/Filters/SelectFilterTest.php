<?php

declare(strict_types=1);

use ModusDigital\LivewireDatatables\Filters\SelectFilter;

class SFUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function getRoleAttribute(): string
    {
        return (string) ($this->attributes['role'] ?? '');
    }

    public function team()
    {
        return $this->belongsTo(SFTeam::class, 'team_id');
    }
}

class SFTeam extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'teams';

    public function getCodeAttribute(): string
    {
        return (string) ($this->attributes['code'] ?? '');
    }
}

it('SelectFilter single en multiple SQL op normale kolom', function () {
    // single
    $query = (new SFUser())->newQuery();
    $f = SelectFilter::make('status');
    $f->apply($query, 'active');
    expect($query->toSql())->toContain('where "users"."status" = ?');

    // multiple
    $query = (new SFUser())->newQuery();
    $f = SelectFilter::make('status')->multiple();
    $f->apply($query, ['active', 'blocked']);
    expect($query->toSql())->toContain('where "users"."status" in (?, ?)');
});

it('SelectFilter relatiepad met whereHas', function () {
    $query = (new SFUser())->newQuery();
    $f = SelectFilter::make('team.name');
    $f->apply($query, 'A');
    $sql = $query->toSql();
    expect($sql)->toContain('exists')
        ->and($sql)->toContain('from "teams"')
        ->and($sql)->toContain('"teams"."name" = ?');
});

it('SelectFilter attribute-pad zet requiresAttributeFiltering en geen SQL where', function () {
    // direct attribute
    $query = (new SFUser())->newQuery();
    $f = SelectFilter::make('role');
    $f->apply($query, 'admin');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('where');

    // relatie attribute
    $query = (new SFUser())->newQuery();
    $f = SelectFilter::make('team.code');
    $f->apply($query, 'X');
    expect($f->requiresAttributeFiltering())->toBeTrue()
        ->and($query->toSql())->not->toContain('exists');
});
