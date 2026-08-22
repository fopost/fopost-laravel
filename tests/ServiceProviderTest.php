<?php

declare(strict_types=1);

namespace Fopost\Laravel\Tests;

use Fopost\Laravel\FopostServiceProvider;
use Fopost\Sdk\Client;
use Fopost\Sdk\Http\HttpClient;
use Illuminate\Support\ServiceProvider;
use ReflectionProperty;

final class ServiceProviderTest extends TestCase
{
    public function test_it_boots_and_merges_the_package_config(): void
    {
        $this->assertSame('https://api.fopost.com', config('fopost.base_url'));
        $this->assertSame(30.0, config('fopost.timeout'));
        $this->assertSame(3, config('fopost.max_retries'));
    }

    public function test_it_publishes_the_config_file(): void
    {
        $paths = ServiceProvider::pathsToPublish(FopostServiceProvider::class, 'fopost-config');

        $this->assertCount(1, $paths);
        $this->assertSame(config_path('fopost.php'), reset($paths));
        $this->assertFileExists(key($paths));
    }

    public function test_the_client_is_a_singleton(): void
    {
        $this->assertSame($this->app->make(Client::class), $this->app->make(Client::class));
        $this->assertSame($this->app->make(Client::class), $this->app->make('fopost'));
    }

    public function test_the_client_is_built_from_config(): void
    {
        config()->set('fopost.api_key', 'fp_from_config');
        config()->set('fopost.base_url', 'https://api.example.test');
        $this->app->forgetInstance(Client::class);

        $client = $this->app->make(Client::class);

        $this->assertSame('https://api.example.test/api/v1', $client->baseUrl());
        $this->assertSame('fp_from_config', $this->headersOf($client)['X-API-Key']);
    }

    public function test_a_base_url_that_already_carries_the_api_path_is_left_alone(): void
    {
        config()->set('fopost.base_url', 'https://api.example.test/api/v1');
        $this->app->forgetInstance(Client::class);

        $this->assertSame('https://api.example.test/api/v1', $this->app->make(Client::class)->baseUrl());
    }

    /**
     * @return array<string, string>
     */
    private function headersOf(Client $client): array
    {
        $property = new ReflectionProperty(Client::class, 'http');
        $property->setAccessible(true);
        $http = $property->getValue($client);
        $this->assertInstanceOf(HttpClient::class, $http);

        return $http->headers();
    }
}
