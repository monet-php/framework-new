<?php

namespace Monet\Framework\Theme\Facades;

use Illuminate\Support\Facades\Facade;

class Themes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'monet.themes';
    }
}
