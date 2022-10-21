<?php

namespace Monet\Framework\Theme\Installer;

interface ThemeInstallerInterface
{
    public function install(string $path, ?string &$error = null): ?string;

    public function publish(array $providers, bool $migrate = true): bool;
}
