<?php

namespace Monet\Framework\Module\Repository;

use Monet\Framework\Module\Module;

interface ModuleRepositoryInterface
{
    public function all(): array;

    public function ordered(): array;

    public function enabled(): array;

    public function disabled(): array;

    public function find(string $name): ?Module;

    public function findOrFail(string $name): Module;

    public function enable(string|Module $module, bool $reset = true): ?string;

    public function disable(string|Module $module, bool $reset = true): ?string;

    public function validate(string|Module $module): ?string;

    public function install(string $path, ?string &$error = null): ?Module;

    public function delete(string|Module $module): ?string;

    public function publish(string|Module $module, bool $migrate = true): ?string;

    public function boot(): void;

    public function reset(bool $clearCache = true): void;

    public function clearCache(): void;
}
