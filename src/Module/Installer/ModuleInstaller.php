<?php

namespace Monet\Framework\Module\Installer;

use Illuminate\Support\Arr;
use Monet\Framework\Installer\ComponentInstaller;
use Monet\Framework\Module\Facades\Modules;

class ModuleInstaller extends ComponentInstaller implements ModuleInstallerInterface
{
    public const MODULE_NOT_FOUND = 'monet.modules.installer.not_found';

    public const MANIFEST_NOT_FOUND = 'monet.modules.installer.manifest_not_found';

    public const INVALID_MANIFEST = 'monet.modules.installer.invalid_manifest';

    public const MODULE_ALREADY_INSTALLED = 'monet.modules.installer.already_installed';

    public const INVALID_PATHS_CONFIG = 'monet.modules.installer.invalid_paths_config';

    public const EXTRACTION_FAILED = 'monet.modules.installer.extraction_failed';

    public function install(string $path, ?string &$error = null): ?string
    {
        if (!$zip = $this->getArchive($path)) {
            $error = static::MODULE_NOT_FOUND;

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

        if (Modules::find($name) !== null) {
            $error = static::MODULE_ALREADY_INSTALLED;

            return null;
        }

        $paths = config('monet.modules.paths');
        if (empty($paths)) {
            $error = static::INVALID_PATHS_CONFIG;

            return null;
        }

        if (!$this->extract($zip, $name, base_path(Arr::first($paths)))) {
            $error = static::EXTRACTION_FAILED;

            return null;
        }

        return $name;
    }

    protected function validate(array $manifest): bool
    {
        return rescue(static function () use ($manifest) {
            if (!isset($manifest['name'])) {
                return false;
            }

            if (!isset($manifest['extra'])) {
                return false;
            }

            if (!isset($manifest['extra']['monet'])) {
                return false;
            }

            return isset($manifest['extra']['monet']['module']);
        }, false);
    }
}
