<?php

namespace App\Enums;

enum MediaApiSource: string
{
    case Discogs = 'discogs';
    case Omdb = 'omdb';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Discogs => 'Discogs',
            self::Omdb => 'OMDb',
            self::None => 'None',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}
