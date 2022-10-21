<?php

namespace Monet\Framework\Setting\Drivers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;

class SettingsFileDriver implements SettingsDriverInterface
{
    protected Filesystem $files;

    protected string $path;

    protected ?array $data = null;

    protected array $updated = [];

    protected array $deleted = [];

    public function __construct(FilesystemManager $storage, string $disk, string $path)
    {
        $this->files = $storage->disk($disk);
        $this->path = $path;
    }

    public function pull(string $key, $default = null)
    {
        $value = $this->get($key, $default);

        $this->forget($key);

        return $value;
    }

    public function get(string $key, $default = null)
    {
        if ($this->data === null) {
            $this->data = $this->load();
        }

        return Arr::get($this->data, $key, $default);
    }

    protected function load(): array
    {
        if (!$this->files->exists($this->path)) {
            return [];
        }

        return json_decode($this->files->get($this->path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function forget(string $key): void
    {
        if ($this->data === null) {
            $this->data = $this->load();
        }

        Arr::forget($this->data, $key);

        $this->deleted[] = $key;
    }

    public function save(): void
    {
        if (empty($this->updated) && empty($this->deleted)) {
            return;
        }

        $this->files->put($this->path, collect($this->data)->toJson());
    }

    public function put(string $key, $value): void
    {
        if ($this->data === null) {
            $this->data = $this->load();
        }

        Arr::set($this->data, $key, $value);

        $this->updated[] = $key;
    }
}
