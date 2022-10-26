<?php

namespace Monet\Framework\Admin\Providers;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Monet\Framework\Admin\Filament\Resources\ModuleResource;
use Monet\Framework\Admin\Filament\Resources\ThemeResource;
use Monet\Framework\Admin\Filament\Resources\UserResource;
use Monet\Framework\Monet;

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

        Monet::groups([
            20 => 'Users',
            40 => 'Appearance',
            60 => 'Extend',
            80 => 'Administration'
        ]);

        $this->app->resolving('filament', static function () {
            Filament::serving(static function () {
                Filament::registerNavigationGroups(Monet::getGroups());

                Filament::registerTheme(
                    mix('css/monet.css', 'monet'),
                );
            });
        });
    }
}
