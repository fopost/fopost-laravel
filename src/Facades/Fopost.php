<?php

declare(strict_types=1);

namespace Fopost\Laravel\Facades;

use Fopost\Sdk\Client;
use Illuminate\Support\Facades\Facade;

/**
 * Static access to the FoPost Cloud API client.
 *
 *     use Fopost\Laravel\Facades\Fopost;
 *
 *     $accounts = Fopost::accounts()->list($workspaceId);
 *
 * @method static \Fopost\Sdk\Resource\PostsResource posts()
 * @method static \Fopost\Sdk\Resource\AccountsResource accounts()
 * @method static \Fopost\Sdk\Resource\WorkspacesResource workspaces()
 * @method static \Fopost\Sdk\Resource\LabelsResource labels()
 * @method static \Fopost\Sdk\Resource\AiResource ai()
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
}
