<?php

namespace Monet\Framework\Theme\Installer;

use Illuminate\Support\Arr;
use Monet\Framework\Installer\ComponentInstaller;
use Monet\Framework\Theme\Facades\Themes;

class ThemeInstaller extends ComponentInstaller implements ThemeInstallerInterface
{
    public const THEME_NOT_FOUND = 'monet::theme.not_found';

    public const MANIFEST_NOT_FOUND = 'monet::theme.manifest_not_found';

    public const INVALID_MANIFEST = 'monet::theme.installer.invalid_manifest';

    public const THEME_ALREADY_INSTALLED = 'monet::theme.installer.already_installed';

    public const INVALID_PATHS_CONFIG = 'monet::theme.installer.invalid_paths_config';

    public const EXTRACTION_FAILED = 'monet::theme.installer.extraction_failed';

    protected array $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function install(string $path, ?string &$error = null): ?string
    {
        if (empty($this->paths)) {
            $error = static::INVALID_PATHS_CONFIG;

            return null;
        }

        if (!$zip = $this->getArchive($path)) {
            $error = static::THEME_NOT_FOUND;

            return null;
        }

        if (!$index = $this->findManifestIndex($zip)) {
            $error = static::MANIFEST_NOT_FOUND;

            return null;
        }

        if (!($manifest = $this->getManifest($zip, $index))) {
            $error = static::INVALID_MANIFEST;

            return null;
        }

        if (!$this->validate($manifest)) {
            $error = static::INVALID_MANIFEST;

            return null;
        }

        $name = $manifest['name'];

        if (Themes::find($name) !== null) {
            $error = static::THEME_ALREADY_INSTALLED;

            return null;
        }

        if (!$this->extract($zip, $name, base_path(Arr::first($this->paths)))) {
            $error = static::EXTRACTION_FAILED;

            return null;
        }

        return $name;
    }

    protected function validate(array $manifest): bool
    {
        return rescue(static function () use ($manifest) {
            return isset(
                $manifest['name'],
                $manifest['description'],
                $manifest['version'],
                $manifest['extra']['monet']['theme']
            );
        }, false);
    }
}
