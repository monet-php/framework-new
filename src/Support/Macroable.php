<?php

namespace Monet\Framework\Support;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

trait Macroable
{
    protected static array $macros = [];

    protected static array $macroCallbacks = [];

    protected static bool $macrosBooted = false;

    public static function mixin(object|string $mixin): void
    {
        $class = new ReflectionClass($mixin);

        $methods = $class->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            $method->setAccessible(true);
            static::macro($method->name, $method->invoke($mixin));
        }

        $properties = $class->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
        );

        foreach ($properties as $property) {
            $property->setAccessible(true);
            static::extend(function () use ($class, $property) {
                $this->{$property->getName()} = $property->getValue($class);
            });
        }
    }

    public static function macro(string $name, object|callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    public static function extend(callable $callback): void
    {
        static::$macroCallbacks[] = $callback;
    }

    public static function __callStatic($method, $parameters)
    {
        if (!static::hasExtension($method)) {
            if (method_exists(get_parent_class(), '__callStatic')) {
                return parent::__callStatic($method, $parameters);
            }

            throw new BadMethodCallException(
                sprintf(
                    'Method "%s" does not exist.',
                    $method
                )
            );
        }

        $extension = static::$macros[$method];

        if ($extension instanceof Closure) {
            return call_user_func_array(
                Closure::bind($extension, null, static::class),
                $parameters
            );
        }

        return call_user_func_array($extension, $parameters);
    }

    public static function hasExtension(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    public function __call($method, $parameters)
    {
        $this->macroBoot();

        if (!static::hasExtension($method)) {
            if (method_exists(get_parent_class(), '__call')) {
                return parent::__call($method, $parameters);
            }

            throw new BadMethodCallException(
                sprintf(
                    'Method "%s" does not exist.',
                    $method
                )
            );
        }

        $extension = static::$macros[$method];

        if ($extension instanceof Closure) {
            return call_user_func_array(
                $extension->bindTo($this, static::class),
                $parameters
            );
        }

        return call_user_func_array($extension, $parameters);
    }

    public function macroBoot(): void
    {
        if (static::$macrosBooted) {
            return;
        }

        foreach (static::$macroCallbacks as $callback) {
            call_user_func($callback->bindTo($this, static::class), $this);
        }

        static::$macrosBooted = true;
    }

    public function __isset($key)
    {
        $this->macroBoot();

        if (method_exists(get_parent_class(), '__isset')) {
            return parent::__isset($key);
        }

        return isset($this->{$key});
    }

    public function __get($key)
    {
        $this->macroBoot();

        if (method_exists(get_parent_class(), '__get')) {
            return parent::__get($key);
        }

        return $this->{$key};
    }

    public function __set($key, $value)
    {
        $this->macroBoot();

        if (method_exists(get_parent_class(), '__set')) {
            parent::__set($key, $value);
        } else {
            $this->{$key} = $value;
        }
    }
}
