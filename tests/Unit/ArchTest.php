<?php

declare(strict_types=1);

arch('it will not use debugging functions')->expect(['dd', 'dump', 'ray'])->not->toBeUsed();

arch('Enums in src/Enums eindigen niet op Enum')->expect('ModusDigital\\LivewireDatatables\\Enums')
    ->toBeEnums()
    ->not->toHaveSuffix('Enum');

arch('Geen nieuw gebruik van deprecated methods buiten Column')->expect('ModusDigital\\LivewireDatatables')
    ->classes()
    ->not->toUse(['ModusDigital\\LivewireDatatables\\Columns\\Column::relationship', 'ModusDigital\\LivewireDatatables\\Columns\\Column::getRelationship'])
    ->ignoring('ModusDigital\\LivewireDatatables\\Columns\\Column');
