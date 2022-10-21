<?php

namespace Monet\Framework\Setting;

use Illuminate\Support\Manager;
use Monet\Framework\Setting\Drivers\SettingsFileDriver;

class SettingsManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('monet.settings.driver');
    }

    public function createFileDriver(): SettingsFileDriver
    {
        return $this->container->make(SettingsFileDriver::class);
    }
}
