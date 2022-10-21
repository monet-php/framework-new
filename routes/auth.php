<?php

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Monet\Framework\Auth\Http\Controller\EmailVerificationController;
use Monet\Framework\Auth\Http\Controller\LogoutController;

$routes = config('monet.auth.routes');
if ($routes === null) {
    return;
}

Route::middleware('web')->group(function () use ($routes) {
    $passwordRoutes = $routes['password'] ?? [];

    Route::middleware('guest')->group(function () use ($routes, $passwordRoutes) {
        $loginRoute = $routes['login'] ?? null;
        $registerRoute = $routes['register'] ?? null;
        $passwordRequestRoute = $passwordRoutes['request'] ?? null;
        $resetPasswordRoute = $passwordRoutes['reset'] ?? null;

        if ($loginRoute !== null) {
            Route::get($loginRoute, fn(): View => view('monet::auth.login'))
                ->name('login');
        }

        if ($registerRoute !== null) {
            Route::get($registerRoute, fn(): View => view('monet::auth.register'))
                ->name('register');
        }

        if ($passwordRequestRoute !== null) {
            Route::get(
                $passwordRequestRoute,
                fn(): View => view('monet::auth.password-request')
            )->name('password.request');
        }

        if ($resetPasswordRoute !== null) {
            Route::get(
                $resetPasswordRoute . '/{token}',
                fn(): View => view('monet::auth.password-reset')
            )->name('password.reset');
        }
    });

    Route::middleware('auth')->group(function () use ($routes, $passwordRoutes) {
        $logoutRoute = $routes['logout'] ?? null;

        $emailRoutes = $routes['email'] ?? [];
        $emailNoticeRoute = $emailRoutes['notice'] ?? null;
        $emailVerifyRoute = $emailRoutes['verify'] ?? null;

        $passwordConfirmationRoute = $passwordRoutes['confirm'];

        if ($logoutRoute !== null) {
            Route::post(
                $logoutRoute,
                LogoutController::class
            )->name('logout');
        }

        if ($emailNoticeRoute !== null) {
            Route::get($emailNoticeRoute, fn(): View => view('monet::auth.email-verification'))
                ->name('email-verification.notice');
        }

        if ($emailVerifyRoute !== null) {
            Route::post($emailVerifyRoute, EmailVerificationController::class)
                ->middleware(['signed', 'throttle:5,1'])
                ->name('email-verification.store');
        }

        if ($passwordConfirmationRoute !== null) {
            Route::get(
                $passwordConfirmationRoute,
                fn(): View => view('monet::auth.password-confirmation')
            )->name('password.confirm');
        }
    });
});
