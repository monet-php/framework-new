<?php

namespace Monet\Framework\Setting\Drivers;

interface SettingsDriverInterface
{
    public function get(string $key, $default = null);

    public function put(string $key, $value): void;

    public function pull(string $key, $default = null);

    public function forget(string $key): void;

    public function save(): void;
}
