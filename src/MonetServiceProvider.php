<?php

namespace Monet\Framework;

use Illuminate\Support\AggregateServiceProvider;
use Monet\Framework\Admin\Providers\AdminServiceProvider;
use Monet\Framework\Auth\Providers\AuthServiceProvider;
use Monet\Framework\Module\Providers\ModulesServiceProvider;
use Monet\Framework\Setting\Providers\SettingsServiceProvider;
use Monet\Framework\Support\Filesystem;
use Monet\Framework\Theme\Facades\Themes;
use Monet\Framework\Theme\Providers\ThemeServiceProvider;
use Monet\Framework\Transformer\Providers\TransformerServiceProvider;

class MonetServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        TransformerServiceProvider::class,
        SettingsServiceProvider::class,
        AuthServiceProvider::class,
        AdminServiceProvider::class,
        ModulesServiceProvider::class,
        ThemeServiceProvider::class
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/monet.php', 'monet');

        $this->app->alias(Filesystem::class, 'files');
        $this->app->singleton(Filesystem::class);

        parent::register();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'monet'
        );

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'monet');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../dist' => public_path('monet'),
            ], 'assets');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/monet.php' => config_path('monet.php'),
                ],
                'config'
            );

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/monet')
            ]);
        }

        $this->enableIntroduction();
    }

    protected function enableIntroduction(): void
    {
        if (Themes::enabled() !== null) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/introduction.php');
    }

    public function provides(): array
    {
        return [
            Filesystem::class,
            ...parent::provides()
        ];
    }
}
