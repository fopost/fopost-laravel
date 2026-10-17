<?php

declare(strict_types=1);

namespace Fopost\Laravel;

use Fopost\Laravel\Blog\ArticleReceiver;
use Fopost\Sdk\Client;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Route as RegisteredRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the FoPost Cloud API client into the container.
 *
 * Auto-discovered by Laravel. It binds Fopost\Sdk\Client, built from
 * config/fopost.php, and the article receiver behind Route::fopostArticles().
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

        $this->app->singleton(ArticleReceiver::class, static function ($app): ArticleReceiver {
            return new ArticleReceiver(
                $app->make(Dispatcher::class),
                (string) ($app['config']->get('fopost.website_secret') ?? ''),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([self::configPath() => $this->app->configPath('fopost.php')], 'fopost-config');
        }

        // FoPost signs the body itself, so the session CSRF token must not apply.
        Route::macro('fopostArticles', static function (string $uri): RegisteredRoute {
            return Route::post($uri, ArticleReceiver::class)
                ->name('fopost.articles')
                ->withoutMiddleware([ValidateCsrfToken::class]);
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Client::class, 'fopost', ArticleReceiver::class];
    }

    private static function configPath(): string
    {
        return dirname(__DIR__) . '/config/fopost.php';
    }
}
