<?php

declare(strict_types=1);

namespace Fopost\Laravel;

use Fopost\Sdk\Client;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the FoPost Cloud API client into the container.
 *
 * Auto-discovered by Laravel. It binds one thing: Fopost\Sdk\Client, built
 * from config/fopost.php.
 */
final class FopostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(self::configPath(), 'fopost');

        $this->app->singleton(Client::class, static function ($app): Client {
            $config = $app['config']->get('fopost', []);

            return new Client(
                apiKey: $config['api_key'] ?? null,
                baseUrl: $config['base_url'] ?? Client::DEFAULT_BASE_URL,
                timeout: (float) ($config['timeout'] ?? 30.0),
                maxRetries: (int) ($config['max_retries'] ?? 3),
            );
        });

        $this->app->alias(Client::class, 'fopost');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([self::configPath() => $this->app->configPath('fopost.php')], 'fopost-config');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Client::class, 'fopost'];
    }

    private static function configPath(): string
    {
        return dirname(__DIR__) . '/config/fopost.php';
    }
}
