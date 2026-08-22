<?php

declare(strict_types=1);

namespace Fopost\Laravel\Tests;

use Fopost\Laravel\FopostServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [FopostServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['Fopost' => \Fopost\Laravel\Facades\Fopost::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('fopost.api_key', 'fop_live_test');
    }
}
