<?php

namespace Monet\Framework;

use Filament\Navigation\NavigationGroup;

class Monet
{
    protected static array $navigationGroups = [];

    public static function group(string|NavigationGroup $group, int $order = 0): void
    {
        static::$navigationGroups[$order][] = $group;
    }

    public static function groups(array $groups): void
    {
        foreach ($groups as $order => $group) {
            if (is_array($group)) {
                static::$navigationGroups[$order] = array_merge(static::$navigationGroups[$order] ?? [], $group);
            } else {
                static::$navigationGroups[$order][] = $group;
            }
        }
    }

    public static function getGroups(): array
    {
        ksort(static::$navigationGroups);

        $groups = [];
        foreach (static::$navigationGroups as $group) {
            $groups[] = $group;
        }

        return $groups;
    }
}
