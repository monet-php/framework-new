<?php

namespace Monet\Framework\Auth\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Monet\Framework\Auth\Contracts\ShouldVerifyEmail;

class EnsureEmailIsVerifiedIfRequired
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $user = $request->user();

        if (
            ! $user ||
            (
                $user instanceof ShouldVerifyEmail &&
                $user->shouldVerifyEmail() &&
                ! $user->hasVerifiedEmail()
            )
        ) {
            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'email-verification.notice'));
        }

        return $next($request);
    }
}
