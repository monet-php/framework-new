<?php

namespace Monet\Framework\Theme\Loader;

use Monet\Framework\Theme\Theme;

interface ThemeLoaderInterface
{
    public function fromPath(string $path): Theme;

    public function fromArray(array $theme): Theme;
}
