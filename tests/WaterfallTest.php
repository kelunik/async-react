<?php

namespace React\Tests\Async;

use React;
use React\EventLoop\Loop;

class WaterfallTest extends TestCase
{
    public function testWaterfallWithoutTasks()
    {
        $tasks = array();

        $callback = $this->expectCallableOnce();
        $errback = $this->expectCallableNever();

        React\Async\waterfall($tasks, $callback, $errback);
    }

    public function testWaterfallWithTasks()
    {
        $tasks = array(
            function ($callback, $errback) {
                Loop::addTimer(0.05, function () use ($callback) {
                    $callback('foo');
                });
            },
            function ($foo, $callback, $errback) {
                Loop::addTimer(0.05, function () use ($callback, $foo) {
                    $callback($foo.'bar');
                });
            },
            function ($bar, $callback, $errback) {
                Loop::addTimer(0.05, function () use ($callback, $bar) {
                    $callback($bar.'baz');
                });
            },
        );

        $callback = $this->expectCallableOnceWith('foobarbaz');
        $errback = $this->expectCallableNever();

        React\Async\waterfall($tasks, $callback, $errback);

        $timer = new Timer($this);
        $timer->start();

        Loop::run();

        $timer->stop();
        $timer->assertInRange(0.15, 0.30);
    }

    public function testWaterfallWithError()
    {
        $called = 0;

        $tasks = array(
            function ($callback, $errback) use (&$called) {
                $callback('foo');
                $called++;
            },
            function ($foo, $callback, $errback) {
                $e = new \RuntimeException('whoops');
                $errback($e);
            },
            function ($callback, $errback) use (&$called) {
                $callback('bar');
                $called++;
            },
        );

        $callback = $this->expectCallableNever();
        $errback = $this->expectCallableOnce();

        React\Async\waterfall($tasks, $callback, $errback);

        $this->assertSame(1, $called);
    }
}