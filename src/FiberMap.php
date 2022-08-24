<?php

namespace React\Async;

use React\Promise\PromiseInterface;

/**
 * @internal
 */
final class FiberMap
{
    private static array $map = [];

    public static function register(\Fiber $fiber): void
    {
        self::$map[\spl_object_id($fiber)] = [];
    }

    public static function setPromise(\Fiber $fiber, PromiseInterface $promise): void
    {
        self::$map[\spl_object_id($fiber)] = $promise;
    }

    public static function unsetPromise(\Fiber $fiber, PromiseInterface $promise): void
    {
        unset(self::$map[\spl_object_id($fiber)]);
    }

    public static function getPromise(\Fiber $fiber): ?PromiseInterface
    {
        return self::$map[\spl_object_id($fiber)] ?? null;
    }

    public static function unregister(\Fiber $fiber): void
    {
        unset(self::$map[\spl_object_id($fiber)]);
    }
}
