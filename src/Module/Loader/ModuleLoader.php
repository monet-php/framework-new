<?php

namespace Monet\Framework\Module\Loader;

use Monet\Framework\Module\Module;
use Monet\Framework\Support\Json;

class ModuleLoader implements ModuleLoaderInterface
{
    public function fromPath(string $path): Module
    {
        $json = Json::make($path);

        return Module::make(
            $json->get('name'),
            dirname($path),
            $json->get('description'),
            $json->get('version'),
            false,
            $json->get('extra.monet.module.providers', []),
            $json->get('extra.monet.module.dependencies', [])
        );
    }

    public function fromArray(array $module): Module
    {
        return Module::make(
            $module['name'],
            $module['path'],
            $module['description'],
            $module['version'],
            $module['enabled'],
            $module['providers'],
            $module['dependencies']
        );
    }
}
