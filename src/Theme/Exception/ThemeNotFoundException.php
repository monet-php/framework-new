<?php

namespace Monet\Framework\Theme\Exception;

use Exception;

class ThemeNotFoundException extends Exception
{
    public static function theme(string $name): static
    {
        return new static(
            sprintf('Failed to find theme "%s".', $name)
        );
    }
}
