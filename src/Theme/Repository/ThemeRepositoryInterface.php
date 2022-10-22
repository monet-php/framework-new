<?php

namespace Monet\Framework\Theme\Repository;

use Monet\Framework\Theme\Theme;

interface ThemeRepositoryInterface
{
    public function all(): array;

    public function enabled(): ?Theme;

    public function disabled(): array;

    public function parent(): ?Theme;

    public function find(string $name): ?Theme;

    public function findOrFail(string $name): Theme;

    public function enable(string|Theme $theme, bool $reset = true): ?string;

    public function disable(bool $reset = true): ?string;

    public function validate(string|Theme $theme): ?string;

    public function install(string $path, ?string &$error = null): ?Theme;

    public function delete(string|Theme $theme): ?string;

    public function publish(string|Theme $module, bool $migrate = true): ?string;

    public function boot(): void;

    public function reset(bool $clearCache = true): void;

    public function clearCache(): void;
}
