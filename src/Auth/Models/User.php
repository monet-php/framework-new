<?php

namespace Monet\Framework\Auth\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Monet\Framework\Auth\Contracts\ShouldVerifyEmail;
use Monet\Framework\Support\Macroable;
use Monet\Framework\Transformer\Facades\Transformer;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements ShouldVerifyEmail, FilamentUser, HasName
{
    use Macroable;
    use HasRoles;
    use Notifiable;

    public function getFillable(): array
    {
        return Transformer::transform(
            'monet.auth.user.model.fillable',
            [
                'name',
                'email',
                'password',
                'email_verified_at',
            ]
        );
    }

    public function getHidden(): array
    {
        return Transformer::transform(
            'monet.auth.user.model.hidden',
            [
                'password',
                'remember_token',
            ]
        );
    }

    public function getCasts(): array
    {
        return Transformer::transform(
            'monet.auth.user.model.casts',
            [
                'email_verified_at' => 'datetime',
            ]
        );
    }

    public function getAuthPassword()
    {
        return $this->{static::getAuthPasswordName()};
    }

    public static function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function shouldVerifyEmail(): bool
    {
        return Transformer::transform(
            'monet.auth.user.model.shouldVerifyEmail',
            config('monet.auth.require_email_verification')
        );
    }

    public function canAccessFilament(): bool
    {
        return $this->hasPermissionTo('view admin');
    }

    public function getFilamentName(): string
    {
        return $this->getUsername();
    }

    public function getUsername(): string
    {
        return $this->{static::getUsernameName()};
    }

    public static function getUsernameName(): string
    {
        return 'name';
    }
}
