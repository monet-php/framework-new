<?php

namespace Monet\Framework\Transformer\Repository;

use Closure;
use Illuminate\Contracts\Foundation\Application;

class TransformerRepository implements TransformerRepositoryInterface
{
    protected Application $app;

    protected array $callbacks = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(string $name, array|string|Closure $callback): void
    {
        $this->callbacks[$name][] = $callback;
    }

    public function transform(string $name, $value, ...$parameters)
    {
        $callbacks = $this->callbacks[$name] ?? [];

        foreach ($callbacks as $callback) {
            $value = $this->resolve($callback, $value, $parameters);
        }

        return $value;
    }

    protected function resolve(
        array|string|Closure $callback,
                             $value,
        array $parameters
    ) {
        return $this->app->call($callback, [
            ...$parameters,
            'value' => $value,
        ]);
    }
}
