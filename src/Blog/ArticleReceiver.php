<?php

declare(strict_types=1);

namespace Fopost\Laravel\Blog;

use Closure;
use Fopost\Laravel\Events\ArticlePublished;
use Fopost\Sdk\Blog;
use Fopost\Sdk\Exception\WebhookSignatureException;
use Fopost\Sdk\Model\BlogArticleWebhookPayload;
use Fopost\Sdk\Webhooks;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The endpoint FoPost posts published articles to when the blog targets your website.
 *
 * Mount it with Route::fopostArticles('/fopost/articles'). Verification is the
 * parent SDK's; this class only reads the request and hands the article on.
 */
final class ArticleReceiver
{
    private ?Closure $onPublished = null;

    public function __construct(
        private readonly Dispatcher $events,
        private readonly string $secret,
    ) {
    }

    /** @param callable(BlogArticleWebhookPayload): (string|null|void) $callback may return the live URL */
    public function onPublished(callable $callback): void
    {
        $this->onPublished = $callback(...);
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $event = Webhooks::verify($request->getContent(), $request->headers->all(), $this->secret);
        } catch (WebhookSignatureException $e) {
            return new JsonResponse(['error' => $e->getErrorCode()], 401);
        }

        if ($event->event !== Blog::ARTICLE_PUBLISHED_EVENT) {
            return new JsonResponse(['ok' => true]);
        }

        $article = $event->article();
        $url = $this->onPublished === null ? null : ($this->onPublished)($article);
        $responses = $this->events->dispatch(new ArticlePublished($article));

        // Listeners can answer the live URL too, when no callback did.
        if (!is_string($url) && is_array($responses)) {
            $url = array_values(array_filter($responses, is_string(...)))[0] ?? null;
        }

        return new JsonResponse(is_string($url) ? ['url' => $url] : ['ok' => true]);
    }
}
