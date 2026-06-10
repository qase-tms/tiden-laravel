<?php

declare(strict_types=1);

namespace Tiden\Laravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tiden\Laravel\TidenServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @return array<int,class-string> */
    protected function getPackageProviders($app): array
    {
        return [TidenServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('tiden.dsn', 'http://test@localhost/proj');
        $app['config']->set('tiden.environment', 'testing');
    }
}
