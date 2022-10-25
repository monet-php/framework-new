<?php

namespace Monet\Framework\Admin\Providers;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Monet\Framework\Admin\Filament\Resources\ModuleResource;
use Monet\Framework\Admin\Filament\Resources\ThemeResource;
use Monet\Framework\Admin\Filament\Resources\UserResource;

class AdminServiceProvider extends PluginServiceProvider
{
    public static string $name = 'monet.admin';

    protected array $resources = [
        ModuleResource::class,
        ThemeResource::class,
        UserResource::class
    ];

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->app->resolving('filament', static function () {
            Filament::serving(static function () {
                Filament::registerNavigationGroups([
                    10 => 'Users',
                    20 => 'Appearance',
                    30 => 'Extend',
                    40 => 'Administration',
                ]);

                Filament::registerTheme(
                    mix('css/monet.css', 'monet'),
                );
            });
        });
    }
}
