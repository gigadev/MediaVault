<?php

namespace App\Enums;

enum FamilyVisibility: string
{
    case Private = 'private';
    case ConnectionsOnly = 'connections_only';
    case PublicBrowsable = 'public_browsable';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::ConnectionsOnly => 'Connections Only',
            self::PublicBrowsable => 'Public (Browsable)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}
