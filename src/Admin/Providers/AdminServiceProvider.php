<?php

namespace Monet\Framework\Admin\Providers;

use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Monet\Framework\Admin\Filament\Resources\ModuleResource;
use Monet\Framework\Admin\Filament\Resources\ThemeResource;

class AdminServiceProvider extends PluginServiceProvider
{
    public static string $name = 'monet.admin';

    protected array $resources = [
        ModuleResource::class,
        ThemeResource::class
    ];

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->app->resolving('filament', function () {
            Filament::serving(static function () {
                Filament::registerNavigationGroups([
                    'Users',
                    'Appearance',
                    'Extend',
                    'Administration',
                ]);

                Filament::registerTheme(
                    mix('css/monet.css', 'monet'),
                );
            });
        });
    }
}
