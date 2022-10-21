<?php

namespace Monet\Framework\Theme\Loader;

use Monet\Framework\Support\Json;
use Monet\Framework\Theme\Theme;

class ThemeLoader implements ThemeLoaderInterface
{
    public function fromPath(string $path): Theme
    {
        $json = Json::make($path);

        return Theme::make(
            $json->get('name'),
            dirname($path),
            $json->get('description'),
            $json->get('version'),
            false,
            $json->get('extra.monet.theme.providers'),
            $json->get('extra.monet.theme.parent')
        );
    }

    public function fromArray(array $theme): Theme
    {
        return Theme::make(
            $theme['name'],
            $theme['path'],
            $theme['description'],
            $theme['version'],
            $theme['enabled'],
            $theme['providers'],
            $theme['parent']
        );
    }
}
