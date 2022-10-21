<?php

namespace Monet\Framework\Module\Installer;

interface ModuleInstallerInterface
{
    public function install(string $path, ?string &$error = null): ?string;

    public function publish(array $providers, bool $migrate = true): bool;
}
