<?php

namespace Monet\Framework\Auth\Http\Controller;

use Filament\Facades\Filament;
use Illuminate\Routing\Controller;
use Monet\Framework\Auth\Http\Responses\LogoutResponse;

class LogoutController extends Controller
{
    public function __invoke()
    {
        Filament::auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}
