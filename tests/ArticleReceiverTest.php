<?php

declare(strict_types=1);

namespace Fopost\Laravel\Tests;

use Fopost\Laravel\Events\ArticlePublished;
use Fopost\Laravel\Facades\Fopost;
use Fopost\Sdk\Model\BlogArticleWebhookPayload;
use Fopost\Sdk\Webhooks;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

final class ArticleReceiverTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('fopost.website_secret', self::SECRET);
        // The web group boots the encrypted session, which needs a key.
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    /**
     * @param  \Illuminate\Routing\Router  $router
     */
    protected function defineRoutes($router): void
    {
        // Inside the web group, the way a routes/web.php mount lands.
        $router->middleware('web')->group(static function (): void {
            Route::fopostArticles('/fopost/articles');
        });
    }

    public function test_a_signed_test_delivery_is_accepted(): void
    {
        $response = $this->deliver('{"event":"article.test","data":{},"timestamp":"2026-10-11T10:00:00Z"}');

        $response->assertOk()->assertExactJson(['ok' => true]);
    }

    public function test_a_tampered_delivery_is_refused(): void
    {
        $body = '{"event":"article.test","data":{},"timestamp":"2026-10-11T10:00:00Z"}';
        $headers = $this->signedHeaders($body);

        $response = $this->call('POST', '/fopost/articles', [], [], [], $headers, str_replace('test', 'published', $body));

        $response->assertStatus(401)->assertExactJson(['error' => 'invalid_signature']);
    }

    public function test_a_published_article_reaches_the_callback_and_the_event(): void
    {
        Event::fake([ArticlePublished::class]);
        $received = null;
        Fopost::onArticlePublished(static function (BlogArticleWebhookPayload $article) use (&$received): string {
            $received = $article;

            return 'https://yourbrand.com/blog/' . $article->slug;
        });

        $response = $this->deliver(json_encode([
            'event' => 'article.published',
            'data' => [
                'id' => 'art_1',
                'title' => 'Scheduling from Laravel',
                'slug' => 'scheduling-from-laravel',
                'bodyMarkdown' => '# Hello',
                'bodyHtml' => '<h1>Hello</h1>',
                'tags' => ['laravel'],
                'wordCount' => 1,
                'publishedAt' => '2026-10-11T10:00:00Z',
                'publishedUrl' => 'https://yourbrand.com/blog/scheduling-from-laravel',
            ],
            'timestamp' => '2026-10-11T10:00:00Z',
        ], JSON_THROW_ON_ERROR));

        $response->assertOk()->assertExactJson(['url' => 'https://yourbrand.com/blog/scheduling-from-laravel']);
        $this->assertInstanceOf(BlogArticleWebhookPayload::class, $received);
        $this->assertSame('art_1', $received->id);
        $this->assertSame(['laravel'], $received->tags);
        Event::assertDispatched(ArticlePublished::class, static fn (ArticlePublished $e): bool => $e->article->id === 'art_1');
    }

    private function deliver(string $body): TestResponse
    {
        return $this->call('POST', '/fopost/articles', [], [], [], $this->signedHeaders($body), $body);
    }

    /** @return array<string, string> */
    private function signedHeaders(string $body): array
    {
        $timestamp = time();

        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_FOPOST_EVENT' => 'article.test',
            'HTTP_X_FOPOST_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_FOPOST_SIGNATURE' => Webhooks::sign($body, self::SECRET),
            'HTTP_X_FOPOST_SIGNATURE_256' => Webhooks::signTimestamped($body, self::SECRET, $timestamp),
        ];
    }
}
