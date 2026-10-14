<?php

declare(strict_types=1);

namespace Fopost\Laravel\Events;

use Fopost\Sdk\Model\BlogArticleWebhookPayload;

/** Raised once a verified article.published delivery reaches the receiver. */
final class ArticlePublished
{
    public function __construct(public readonly BlogArticleWebhookPayload $article)
    {
    }
}
