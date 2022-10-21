<?php

namespace Monet\Framework\Theme;

use Illuminate\Contracts\Support\Arrayable;

class Theme implements Arrayable
{
    protected string $name;

    protected string $path;

    protected string $description;

    protected string $version;

    protected bool $enabled;

    protected array $providers;

    protected ?string $parent;

    public function __construct(
        string  $name,
        string  $path,
        string  $description,
        string  $version,
        bool    $enabled,
        array   $providers,
        ?string $parent = null,
    )
    {
        $this->name = $name;
        $this->path = $path;
        $this->description = $description;
        $this->version = $version;
        $this->enabled = $enabled;
        $this->providers = $providers;
        $this->parent = $parent;
    }

    public static function make(
        string  $name,
        string  $path,
        string  $description,
        string  $version,
        bool    $enabled,
        array   $providers,
        ?string $parent = null,
    ): static
    {
        return new static(
            $name,
            $path,
            $description,
            $version,
            $enabled,
            $providers,
            $parent
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(?string $path = null): string
    {
        if ($path === null) {
            return $this->path;
        }

        return $this->path . '/' . trim($path, '/\\');
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disabled(): bool
    {
        return !$this->enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'description' => $this->description,
            'version' => $this->version,
            'enabled' => $this->enabled,
            'providers' => $this->providers,
            'parent' => $this->parent,
        ];
    }
}
