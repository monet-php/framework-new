<?php

namespace Monet\Framework\Module\Repository;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\FixedArraySort;
use Monet\Framework\Module\Exception\ModuleNotFoundException;
use Monet\Framework\Module\Installer\ModuleInstallerInterface;
use Monet\Framework\Module\Loader\ModuleLoaderInterface;
use Monet\Framework\Module\Module;
use Monet\Framework\Setting\Facades\Settings;
use Monet\Framework\Support\Filesystem;

class ModuleRepository implements ModuleRepositoryInterface
{
    public const MODULE_NOT_FOUND = 'monet::module.not_found';

    public const MANIFEST_NOT_FOUND = 'monet::module.manifest_not_found';

    public const INVALID_DEPENDENCY = 'monet::module.invalid_dependency';

    public const DELETE_FAILED = 'monet::module.delete.failed.body';

    public const PUBLISH_FAILED = 'monet::module.publish.failed.body';

    public const INSTALL_FAILED = 'monet::module.install.failed.body';

    protected Application $app;

    protected Filesystem $files;

    protected ModuleLoaderInterface $loader;

    protected ModuleInstallerInterface $installer;

    protected CacheManager $cache;

    protected bool $cacheEnabled;

    protected string $allCacheKey;

    protected string $orderedCacheKey;

    protected array $paths;

    protected ?array $modules = null;

    protected ?array $orderedModules = null;

    public function __construct(
        Application $app,
        Filesystem $files,
        ModuleLoaderInterface $loader,
        ModuleInstallerInterface $installer,
        CacheManager $cache,
        bool $cacheEnabled,
        string $allCacheKey,
        string $orderedCacheKey,
        array $paths,
    )
    {
        $this->app = $app;
        $this->files = $files;
        $this->loader = $loader;
        $this->installer = $installer;
        $this->cache = $cache;
        $this->cacheEnabled = $cacheEnabled;
        $this->allCacheKey = $allCacheKey;
        $this->orderedCacheKey = $orderedCacheKey;
        $this->paths = $paths;
    }

    public function findOrFail(string $name): Module
    {
        if (!$module = $this->find($name)) {
            throw ModuleNotFoundException::module($name);
        }

        return $module;
    }

    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        if (!$this->loadCache()) {
            $this->load();
        }

        return $this->modules;
    }

    protected function loadCache(): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        if (!$cache = $this->cache->get($this->allCacheKey)) {
            return false;
        }

        $this->modules = [];
        foreach ($cache as $name => $module) {
            $this->modules[$name] = $this->loader->fromArray($module);
        }

        return true;
    }

    protected function load(): void
    {
        $this->modules = [];
        foreach ($this->paths as $path) {
            $files = $this->discover(base_path($path));

            foreach ($files as $file) {
                $module = $this->loader->fromPath($file);

                $this->modules[$module->getName()] = $module;
            }
        }

        $enabled = Settings::get('monet.modules', []);
        foreach ($enabled as $name => $status) {
            if ($status) {
                $this->find($name)?->enable();
            } else {
                $this->find($name)?->disable();
            }
        }

        $this->cache->forever($this->allCacheKey, collect($this->modules)->toArray());
    }

    protected function discover(string $path): array
    {
        $search = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . 'composer.json';

        return $this->files->find($search);
    }

    public function enable(string|Module $module, bool $reset = true): ?string
    {
        if (!($module instanceof Module)) {
            $module = $this->find($module);
        }

        if ($module === null) {
            return static::MODULE_NOT_FOUND;
        }

        if ($module->enabled()) {
            return null;
        }

        if ($error = $this->validate($module)) {
            return $error;
        }

        $dependencies = $module->getDependencies();
        foreach ($dependencies as $dependency) {
            $this->enable($dependency, false);
        }

        $module->enable();

        Settings::put('monet.modules.' . $module->getName(), true);

        if ($reset) {
            $this->reset();
        }

        return null;
    }

    public function enabled(): array
    {
        return collect($this->all())
            ->filter(fn(Module $module): bool => $module->enabled())
            ->all();
    }

    public function validate(Module|string $module): ?string
    {
        if (!($module instanceof Module)) {
            $module = $this->find($module);
        }

        if ($module === null) {
            return static::MODULE_NOT_FOUND;
        }

        if (!$this->files->exists($module->getPath('composer.json'))) {
            return static::MANIFEST_NOT_FOUND;
        }

        foreach ($module->getDependencies() as $dependency) {
            if ($this->validate($dependency)) {
                return static::INVALID_DEPENDENCY;
            }
        }

        return null;
    }

    public function reset(bool $clearCache = true): void
    {
        if ($clearCache) {
            $this->clearCache();
        }

        $this->modules = null;
        $this->orderedModules = null;
    }

    public function clearCache(): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $this->cache->forget($this->allCacheKey);
        $this->cache->forget($this->orderedCacheKey);
    }

    public function disable(string|Module $module, bool $reset = true): ?string
    {
        if (!($module instanceof Module)) {
            $module = $this->find($module);
        }

        if ($module === null) {
            return static::MODULE_NOT_FOUND;
        }

        if ($module->disabled()) {
            return null;
        }

        $name = $module->getName();

        $dependencies = $this->ordered();
        foreach ($dependencies as $dependency) {
            if (
                in_array($name, $dependency->getDependencies()) &&
                $error = $this->disable($dependency, false)
            ) {
                return $error;
            }
        }

        $module->disable();

        Settings::put('monet.modules.' . $name, false);

        if ($reset) {
            $this->reset();
        }

        return null;
    }

    public function disabled(): array
    {
        return collect($this->all())
            ->filter(fn(Module $module): bool => $module->disabled())
            ->all();
    }

    public function ordered(): array
    {
        if ($this->orderedModules !== null) {
            return $this->orderedModules;
        }

        if (!$this->loadOrderedCache()) {
            $this->loadOrdered();
        }

        return $this->orderedModules;
    }

    protected function loadOrderedCache(): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        if (!$cache = $this->cache->get($this->orderedCacheKey)) {
            return false;
        }

        $this->orderedModules = [];
        foreach ($cache as $module) {
            $this->orderedModules[] = $this->find($module);
        }

        return true;
    }

    protected function loadOrdered(): void
    {
        $this->orderedModules = [];

        $names = $this->getOrderedNames();
        foreach ($names as $name) {
            if ($module = $this->find($name)) {
                $this->orderedModules[] = $module;
            }
        }

        $this->cache->forever($this->orderedCacheKey, $names);
    }

    protected function getOrderedNames(): array
    {
        $sorter = new FixedArraySort();

        $modules = $this->enabled();
        foreach ($modules as $name => $module) {
            $sorter->add($name, $module->getDependencies());
        }

        $names = [];

        $maxAttempts = count($modules);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                $names = $sorter->sort();
                break;
            } catch (CircularDependencyException $e) {
                foreach ($e->getNodes() as $name) {
                    $this->disable($name);
                }
            } catch (ElementNotFoundException $e) {
                $this->disable($e->getSource());
            }
        }

        return $names;
    }

    public function install(string $path, ?string &$error = null): ?Module
    {
        return rescue(function () use ($path, &$error) {
            if (!($name = $this->installer->install($path, $error))) {
                return null;
            }

            $this->reset();

            $module = $this->find($name);
            if ($module === null) {
                $error = static::MODULE_NOT_FOUND;
                return null;
            }

            if ($error = $this->validate($module)) {
                $this->delete($module);
                return null;
            }

            if ($this->bootModule($module)) {
                $this->installer->publish($module->getProviders());
            }

            return $module;
        }, static function () use (&$error) {
            $error = static::INSTALL_FAILED;
            return null;
        });
    }

    public function delete(Module|string $module): ?string
    {
        if (!($module instanceof Module)) {
            $module = $this->find($module);
        }

        if ($module === null) {
            return static::MODULE_NOT_FOUND;
        }

        if (File::exists($module->getPath()) && !File::deleteDirectory($module->getPath())) {
            return static::DELETE_FAILED;
        }

        Settings::forget('monet.modules.' . $module->getName());

        $this->reset();

        return null;
    }

    protected function bootModule(Module $module): bool
    {
        return rescue(function () use ($module) {
            if ($error = $this->validate($module)) {
                $this->disable($module);
                $this->notifyModuleDisabled($module);

                return false;
            }

            require_once $module->getPath('vendor/autoload.php');

            foreach ($module->getProviders() as $provider) {
                $this->app->register($provider);
            }

            return true;
        }, function () use ($module) {
            $this->disable($module, false);
            $this->notifyModuleDisabled($module);

            return false;
        });
    }

    protected function notifyModuleDisabled(string|Module $module): void
    {
        Filament::serving(static function () use ($module): void {
            $name = $module instanceof Module ? $module->getName() : $module;

            Notification::make()
                ->danger()
                ->title(__('monet::module.boot.failed.title'))
                ->body(__('monet::module.boot.failed.body', ['name' => $name]))
                ->send();
        });
    }

    public function publish(Module|string $module, bool $migrate = true): ?string
    {
        if (!($module instanceof Module)) {
            $module = $this->find($module);
        }

        if ($module === null) {
            return static::MODULE_NOT_FOUND;
        }

        if (!$this->installer->publish($module->getProviders(), $migrate)) {
            return static::PUBLISH_FAILED;
        }

        return null;
    }

    public function boot(): void
    {
        $success = true;
        foreach ($this->ordered() as $module) {
            if (!$this->bootModule($module)) {
                $success = false;
            }
        }

        if (!$success) {
            $this->reset();
        }
    }
}
