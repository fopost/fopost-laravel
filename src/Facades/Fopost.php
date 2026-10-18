<?php

declare(strict_types=1);

namespace Fopost\Laravel\Facades;

use Fopost\Laravel\Blog\ArticleReceiver;
use Fopost\Sdk\Client;
use Fopost\Sdk\Model\BlogArticleWebhookPayload;
use Illuminate\Support\Facades\Facade;

/**
 * Static access to the FoPost Cloud API client.
 *
 *     use Fopost\Laravel\Facades\Fopost;
 *
 *     $accounts = Fopost::accounts()->list($workspaceId);
 *
 * @method static \Fopost\Sdk\Resource\PostsResource posts()
 * @method static \Fopost\Sdk\Resource\QueueResource queue()
 * @method static \Fopost\Sdk\Resource\AccountsResource accounts()
 * @method static \Fopost\Sdk\Resource\ConnectSessionsResource connectSessions()
 * @method static \Fopost\Sdk\Resource\WorkspacesResource workspaces()
 * @method static \Fopost\Sdk\Resource\LabelsResource labels()
 * @method static \Fopost\Sdk\Resource\AiResource ai()
 * @method static \Fopost\Sdk\Resource\AutomationsResource automations()
 * @method static \Fopost\Sdk\Resource\InboxResource inbox()
 * @method static \Fopost\Sdk\Resource\ContactsResource contacts()
 * @method static \Fopost\Sdk\Resource\BroadcastsResource broadcasts()
 * @method static \Fopost\Sdk\Resource\SequencesResource sequences()
 * @method static \Fopost\Sdk\Resource\BlogResource blog()
 * @method static \Fopost\Sdk\Resource\AdsResource ads()
 * @method static \Fopost\Sdk\Resource\MediaResource media()
 * @method static \Fopost\Sdk\Resource\ActivityResource activity()
 * @method static \Fopost\Sdk\Resource\LogsResource logs()
 * @method static \Fopost\Sdk\Resource\WebhooksResource webhooks()
 * @method static \Fopost\Sdk\Resource\GoogleBusinessResource googleBusiness()
 * @method static \Fopost\Sdk\Resource\WhatsappResource whatsapp()
 * @method static string baseUrl()
 * @method static mixed request(string $method, string $path, mixed $json = null, ?array $params = null)
 *
 * @see \Fopost\Sdk\Client
 */
final class Fopost extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }

    /** @param callable(BlogArticleWebhookPayload): (string|null|void) $callback may return the live URL */
    public static function onArticlePublished(callable $callback): void
    {
        static::$app->make(ArticleReceiver::class)->onPublished($callback);
    }
}
