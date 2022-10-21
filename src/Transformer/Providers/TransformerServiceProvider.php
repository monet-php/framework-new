<?php

namespace Monet\Framework\Transformer\Providers;

use Illuminate\Support\ServiceProvider;
use Monet\Framework\Transformer\Repository\TransformerRepository;
use Monet\Framework\Transformer\Repository\TransformerRepositoryInterface;

class TransformerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(TransformerRepositoryInterface::class, 'monet.transformer');
        $this->app->singleton(
            TransformerRepositoryInterface::class,
            TransformerRepository::class
        );
    }

    public function provides(): array
    {
        return [
            TransformerRepositoryInterface::class,
            TransformerRepository::class,
            'monet.transformer',
        ];
    }
}
