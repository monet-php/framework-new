<?php

namespace Monet\Framework\Auth;

use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Support\Macroable;
use Spatie\Permission\Models\Role;

class Auth
{
    use Macroable;

    public static function register(
        array $attributes,
        bool  $loginAfter = true,
        bool  $silently = false
    ): User
    {
        $passwordName = User::getAuthPasswordName();

        $user = User::query()
            ->create([
                ...$attributes,
                $passwordName => Hash::make($attributes[$passwordName] ?? null),
            ]);

        $user->assignRole(Role::findById(1));

        if (!$silently) {
            event(new Registered($user));
        }

        if ($loginAfter) {
            Filament::auth()->login($user);
        }

        return $user;
    }

    public static function login(
        array $credentials,
        bool  $updatePasswordConfirmed = true
    ): void
    {
        $authQuery = [
            function ($query) use ($credentials) {
                $query->orWhere('email', '=', $credentials['email']);
            },
        ];

        $usernameEnabled = config('monet.auth.allow_username_login');

        if ($usernameEnabled) {
            $usernameIdentifier = User::getUsernameName();

            $username = $credentials[$usernameIdentifier] ?? $credentials['email'];

            $authQuery = [
                function ($query) use ($usernameIdentifier, $credentials, $username) {
                    $query->orWhere('email', '=', $credentials['email'])
                        ->orWhere($usernameIdentifier, '=', $username);
                },
            ];
        }

        $passwordName = User::getAuthPasswordName();
        $authQuery[$passwordName] = $credentials[$passwordName] ?? null;

        $remember = $credentials['remember'] ?? false;

        if (!Filament::auth()->attempt($authQuery, $remember)) {
            $validationErrorKey = $usernameEnabled ?
                'monet.auth.errors.emailOrUsername' :
                'monet.auth.errors.email';

            throw ValidationException::withMessages([
                'email' => __($validationErrorKey),
            ]);
        }

        if ($updatePasswordConfirmed) {
            session()->put('auth.password_confirmed_at', time());
        }
    }
}
