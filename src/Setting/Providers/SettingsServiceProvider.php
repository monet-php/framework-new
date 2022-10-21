<?php

namespace Monet\Framework\Setting\Providers;

use Illuminate\Support\ServiceProvider;
use Monet\Framework\Setting\Drivers\SettingsFileDriver;
use Monet\Framework\Setting\SettingsManager;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(SettingsManager::class, 'monet.settings');
        $this->app->singleton(SettingsManager::class);

        $this->app->when(SettingsFileDriver::class)
            ->needs('$disk')
            ->giveConfig('monet.settings.file.disk');

        $this->app->when(SettingsFileDriver::class)
            ->needs('$path')
            ->giveConfig('monet.settings.file.path');

        $this->app->terminating(function () {
            $this->app->make('monet.settings')->save();
        });
    }

    public function provides(): array
    {
        return [
            SettingsManager::class,
            SettingsFileDriver::class,
            'monet.settings',
        ];
    }
}
