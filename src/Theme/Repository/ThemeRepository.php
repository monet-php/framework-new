<?php

namespace Monet\Framework\Theme\Repository;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Monet\Framework\Setting\Facades\Settings;
use Monet\Framework\Support\Filesystem;
use Monet\Framework\Theme\Exception\ThemeNotFoundException;
use Monet\Framework\Theme\Installer\ThemeInstallerInterface;
use Monet\Framework\Theme\Loader\ThemeLoaderInterface;
use Monet\Framework\Theme\Theme;

class ThemeRepository implements ThemeRepositoryInterface
{
    public const THEME_NOT_FOUND = 'monet::theme.not_found';

    public const MANIFEST_NOT_FOUND = 'monet::theme.manifest_not_found';

    public const INVALID_PARENT = 'monet::theme.invalid_parent';

    public const DELETE_FAILED = 'monet::theme.delete_failed';

    public const PUBLISH_FAILED = 'monet::theme.publish_failed';

    protected Application $app;

    protected Filesystem $files;

    protected ThemeLoaderInterface $loader;

    protected ThemeInstallerInterface $installer;

    protected CacheManager $cache;

    protected bool $cacheEnabled;

    protected string $cacheKey;

    protected array $paths;

    protected ?array $themes = null;

    protected ?Theme $enabledTheme = null;

    protected ?Theme $parentTheme = null;

    public function __construct(
        Application             $app,
        Filesystem              $files,
        ThemeLoaderInterface    $loader,
        ThemeInstallerInterface $installer,
        CacheManager            $cache,
        bool                    $cacheEnabled,
        string                  $cacheKey,
        array                   $paths,
    )
    {
        $this->app = $app;
        $this->files = $files;
        $this->loader = $loader;
        $this->installer = $installer;
        $this->cache = $cache;
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheKey = $cacheKey;
        $this->paths = $paths;
    }

    public function disabled(): array
    {
        if (!$enabledTheme = $this->enabled()) {
            return $this->themes;
        }

        return collect($this->themes)
            ->filter(fn(Theme $theme): bool => $theme->getName() !== $enabledTheme->getName())
            ->all();
    }

    public function enabled(): ?Theme
    {
        if (($this->themes === null) && !$this->loadCache()) {
            $this->load();
        }

        return $this->enabledTheme;
    }

    protected function loadCache(): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        if (!$cache = $this->cache->get($this->cacheKey)) {
            return false;
        }

        $this->themes = [];
        foreach ($cache as $name => $theme) {
            $this->themes[$name] = $this->loader->fromArray($theme);
        }

        $this->loadEnabledTheme();

        return true;
    }

    protected function loadEnabledTheme(): void
    {
        $themeName = Settings::get('monet.theme');
        if ($themeName === null) {
            return;
        }

        if (!$this->enabledTheme = $this->find($themeName)) {
            return;
        }

        if (($parentName = $this->enabledTheme->getParent()) && $parent = $this->find($parentName)) {
            $this->parentTheme = $parent;
        }
    }

    public function find(string $name): ?Theme
    {
        return $this->all()[$name] ?? null;
    }

    public function all(): array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        if (!$this->loadCache()) {
            $this->load();
        }

        return $this->themes;
    }

    protected function load(): void
    {
        $this->themes = [];
        foreach ($this->paths as $path) {
            $files = $this->discover(base_path($path));

            foreach ($files as $file) {
                $theme = $this->loader->fromPath($file);

                $this->themes[$theme->getName()] = $theme;
            }
        }

        $this->loadEnabledTheme();
    }

    protected function discover(string $path): array
    {
        $search = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . 'composer.json';

        return $this->files->find($search);
    }

    public function findOrFail(string $name): Theme
    {
        if (!$theme = $this->find($name)) {
            throw ThemeNotFoundException::theme($name);
        }

        return $theme;
    }

    public function enable(Theme|string $theme, bool $reset = true): ?string
    {
        if (!($theme instanceof Theme)) {
            $theme = $this->find($theme);
        }

        if ($theme === null) {
            return static::THEME_NOT_FOUND;
        }

        if ($theme->enabled()) {
            return null;
        }

        if ($error = $this->validate($theme)) {
            return $error;
        }

        if ($parentName = $theme->getParent()) {
            if (!($parentTheme = $this->find($parentName)) || $error = $this->validate($parentTheme)) {
                return $error;
            }

            $this->parentTheme = $parentTheme;
        }

        $theme->enable();

        Settings::put('monet.theme', $theme->getName());

        if ($reset) {
            $this->reset();
        }

        return null;
    }

    public function validate(Theme|string $theme): ?string
    {
        if (!($theme instanceof Theme)) {
            $theme = $this->find($theme);
        }

        if ($theme === null) {
            return static::THEME_NOT_FOUND;
        }

        if (!$this->files->exists($theme->getPath('composer.json'))) {
            return static::MANIFEST_NOT_FOUND;
        }

        if (($parent = $theme->getParent()) && $this->validate($parent)) {
            return static::INVALID_PARENT;
        }

        return null;
    }

    public function reset(bool $clearCache = true): void
    {
        if ($clearCache) {
            $this->clearCache();
        }

        $this->themes = null;
        $this->enabledTheme = null;
        $this->parentTheme = null;
    }

    public function clearCache(): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $this->cache->forget($this->cacheKey);
    }

    public function install(string $path, ?string &$error = null): ?Theme
    {
        if (!($name = $this->installer->install($path, $error))) {
            return null;
        }

        $this->reset();

        $theme = $this->find($name);
        if ($theme === null) {
            $error = static::THEME_NOT_FOUND;
            return null;
        }

        if ($error = $this->validate($theme)) {
            $this->delete($theme);
            return null;
        }

        require_once $theme->getPath('vendor/autoload.php');
        $this->installer->publish($theme->getProviders());

        return $theme;
    }

    public function delete(Theme|string $theme): ?string
    {
        if (!($theme instanceof Theme)) {
            $theme = $this->find($theme);
        }

        if ($theme === null) {
            return static::THEME_NOT_FOUND;
        }

        if ($theme->enabled()) {
            $this->disable();
        }

        if (File::exists($theme->getPath()) && !File::deleteDirectory($theme->getPath())) {
            return static::DELETE_FAILED;
        }

        $this->reset();

        return null;
    }

    public function disable(bool $reset = true): ?string
    {
        if (!$enabledTheme = $this->enabled()) {
            return static::THEME_NOT_FOUND;
        }

        if ($parent = $this->parent()) {
            $parent->disable();
        }

        $enabledTheme->disable();

        Settings::forget('monet.theme');

        if ($reset) {
            $this->reset();
        }

        return null;
    }

    public function parent(): ?Theme
    {
        if (($this->themes === null) && !$this->loadCache()) {
            $this->load();
        }

        return $this->parentTheme;
    }

    public function publish(Theme|string $theme, bool $migrate = true): ?string
    {
        if (!($theme instanceof Theme)) {
            $theme = $this->find($theme);
        }

        if ($theme === null) {
            return static::THEME_NOT_FOUND;
        }

        if (!$this->installer->publish($theme->getProviders(), $migrate)) {
            return static::PUBLISH_FAILED;
        }

        return null;
    }

    public function boot(): void
    {
        if (!$enabledTheme = $this->enabled()) {
            return;
        }

        if ($error = $this->validate($enabledTheme)) {
            $this->disable();
            // TODO: Report why theme was disabled
        }

        require_once $enabledTheme->getPath('vendor/autoload.php');

        foreach ($enabledTheme->getProviders() as $provider) {
            $this->app->register($provider);
        }

        if ($parent = $this->parent()) {
            require_once $parent->getPath('vendor/autoload.php');

            foreach ($parent->getProviders() as $provider) {
                $this->app->register($provider);
            }
        }
    }
}
