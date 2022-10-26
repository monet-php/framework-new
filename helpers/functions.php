<?php

use Monet\Framework\Module\Module;
use Monet\Framework\Module\Repository\ModuleRepositoryInterface;
use Monet\Framework\Theme\Repository\ThemeRepositoryInterface;
use Monet\Framework\Theme\Theme;

function settings(?string $key = null, $default = null)
{
    $settings = app('monet.settings');

    if ($key === null) {
        return $settings;
    }

    return $settings->get($key, $default);
}

function settings_pull(string $key, $default = null)
{
    return settings()->pull($key, $default);
}

function settings_forget(string $key): void
{
    settings()->forget($key);
}

function settings_put(string $key, $value): void
{
    settings()->put($key, $value);
}

function modules(): ModuleRepositoryInterface
{
    return app('monet.modules');
}

function module(string $name): ?Module
{
    return modules()->find($name);
}

function module_path(string|Module $module, ?string $path = null): ?string
{
    if (!($module instanceof Module)) {
        $module = module($module);
    }

    return $module?->getPath($path);
}

function themes(): ThemeRepositoryInterface
{
    return app('monet.themes');
}

function theme(string $name): ?Theme
{
    return themes()->find($name);
}

function theme_path(string|Theme $theme, ?string $path = null): ?string
{
    if (!($theme instanceof Theme)) {
        $theme = theme($theme);
    }

    return $theme?->getPath($path);
}
