<?php

namespace Monet\Framework\Auth\Http\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;

class Authenticate extends BaseAuthenticate
{
    protected function redirectTo($request): string
    {
        return route('login');
    }
}
