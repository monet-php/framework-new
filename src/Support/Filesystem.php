<?php

namespace Monet\Framework\Support;

use Illuminate\Filesystem\Filesystem as FilesystemBase;

class Filesystem extends FilesystemBase
{
    public function find(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);

        if ($files) {
            return $files;
        }

        $directories = glob(
            dirname($pattern).DIRECTORY_SEPARATOR.'*',
            GLOB_ONLYDIR | GLOB_NOSORT
        );

        if (! $directories) {
            $directories = [];
        }

        $files = [];
        foreach ($directories as $directory) {
            $files[] = static::find(
                $directory.DIRECTORY_SEPARATOR.basename($pattern),
                $flags
            );
        }

        return array_merge(...$files);
    }
}
