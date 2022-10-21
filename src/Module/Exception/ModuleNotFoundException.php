<?php

namespace Monet\Framework\Module\Exception;

use Exception;

class ModuleNotFoundException extends Exception
{
    public static function module(string $name): static
    {
        return new static(
            sprintf('Failed to find module "%s".', $name)
        );
    }
}
