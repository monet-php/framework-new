<?php

namespace Monet\Framework\Auth\Providers;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Monet\Framework\Auth\Http\Livewire\EmailVerification;
use Monet\Framework\Auth\Http\Livewire\Login;
use Monet\Framework\Auth\Http\Livewire\PasswordConfirmation;
use Monet\Framework\Auth\Http\Livewire\PasswordRequest;
use Monet\Framework\Auth\Http\Livewire\PasswordReset;
use Monet\Framework\Auth\Http\Livewire\Register;
use Monet\Framework\Auth\Http\Responses\LogoutResponse;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LogoutResponseContract::class,
            LogoutResponse::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/auth.php');

        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('monet::auth.login', Login::class);
        Livewire::component('monet::auth.register', Register::class);
        Livewire::component('monet::auth.password-reset', PasswordReset::class);
        Livewire::component('monet::auth.password-request', PasswordRequest::class);
        Livewire::component('monet::auth.password-confirmation', PasswordConfirmation::class);
        Livewire::component('monet::auth.email-verification', EmailVerification::class);
    }
}
