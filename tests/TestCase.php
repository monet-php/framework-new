<?php

namespace Monet\Framework\Tests;

use Filament\FilamentServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Livewire\LivewireServiceProvider;
use Mockery;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Module\Repository\ModuleRepository;
use Monet\Framework\Module\Repository\ModuleRepositoryInterface;
use Monet\Framework\MonetServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName): string => 'Monet\\Framework\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            MonetServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('auth.providers.users.model', User::class);

        Storage::fake('local');

        $app->instance(
            ModuleRepositoryInterface::class,
            Mockery::mock(ModuleRepository::class, function (Mockery\MockInterface $mock) {
                $mock->makePartial();
                $mock->shouldReceive('boot')
                    ->once()
                    ->andReturnUndefined();
            })
        );
    }
}
