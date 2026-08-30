# FoPost for Laravel

[![Packagist](https://img.shields.io/packagist/v/fopost/laravel.svg)](https://packagist.org/packages/fopost/laravel)
[![Downloads](https://img.shields.io/packagist/dt/fopost/laravel.svg)](https://packagist.org/packages/fopost/laravel)
[![CI](https://img.shields.io/github/actions/workflow/status/fopost/fopost-laravel/ci.yml?branch=main&label=ci)](https://github.com/fopost/fopost-laravel/actions)
[![License](https://img.shields.io/packagist/l/fopost/laravel.svg)](https://github.com/fopost/fopost-laravel/blob/main/LICENSE)

Official Laravel SDK for the FoPost API. Schedule and publish to +30 social platforms from your code.

This package is a thin wrapper around [`fopost/sdk`](https://github.com/fopost/fopost-php): it binds the
API client into the container, adds a facade, and gives you a publishable config file. Every platform
connection, token refresh, and delivery happens on the hosted API, so there is nothing to run yourself.

## Requirements

- PHP 8.2 or newer
- Laravel 12
- A FoPost API key from [app.fopost.com/api-keys](https://app.fopost.com/api-keys)

Laravel 10 and 11 are not supported. Those framework lines are out of active support and carry
unpatched security advisories that Composer blocks by default, so there is no version of them
this package can be tested against.

## Installation

```bash
composer require fopost/laravel
```

The service provider and the `Fopost` facade alias are auto-discovered. Publish the config file if you
want to edit it:

```bash
php artisan vendor:publish --tag=fopost-config
```

## Configuration

Add your key to `.env`:

```dotenv
FOPOST_API_KEY=fp_your_key_here
```

Everything else is optional:

| Variable | Default | What it does |
| --- | --- | --- |
| `FOPOST_API_KEY` | none, required | Your API key |
| `FOPOST_API_URL` | `https://api.fopost.com` | API base URL |
| `FOPOST_API_TIMEOUT` | `30` | Seconds to wait for one request |
| `FOPOST_API_MAX_RETRIES` | `3` | Attempts for a rate limited request |

## Quickstart

```php
use Fopost\Laravel\Facades\Fopost;

$workspace = Fopost::workspaces()->list()[0];
$accounts = Fopost::accounts()->list($workspace->id);

$post = Fopost::posts()->create(
    workspaceId: $workspace->id,
    content: 'Shipping today: scheduled posting straight from Laravel.',
    accounts: $accounts,
    status: 'scheduled',
    scheduleAt: now()->addHour(),
);

echo $post->id;
```

Prefer injection over the facade? Type hint the client and the container hands you the configured
singleton:

```php
use Fopost\Sdk\Client;

public function __invoke(Client $fopost)
{
    return $fopost->posts()->list(status: 'scheduled');
}
```

## One example per resource

### Posts

```php
$page = Fopost::posts()->list(workspaceId: $workspaceId, status: 'scheduled');

$post = Fopost::posts()->get($postId);
Fopost::posts()->schedule($postId, now()->addDay());
Fopost::posts()->publish($postId);
Fopost::posts()->delete($postId);

foreach (Fopost::posts()->iterate(workspaceId: $workspaceId) as $everyPost) {
    // walks page by page for you
}
```

### Accounts

```php
$accounts = Fopost::accounts()->list($workspaceId);
$account = Fopost::accounts()->get($accountId);
Fopost::accounts()->disconnect($accountId);
```

### Workspaces

```php
$workspaces = Fopost::workspaces()->list();
$workspace = Fopost::workspaces()->get($workspaceId);
```

### Labels

```php
$label = Fopost::labels()->create($workspaceId, 'Launch week', '#2563eb');
Fopost::labels()->update($label->id, name: 'Launch');
Fopost::labels()->delete($label->id);
```

### AI

```php
$balance = Fopost::ai()->credits();

$caption = Fopost::ai()->generateCaption(
    currentCaption: 'New release is live',
    platforms: ['instagram', 'linkedin'],
    workspaceId: $workspaceId,
);

$rewritten = Fopost::ai()->rewrite(
    content: 'Long form announcement text',
    platforms: ['x', 'threads'],
    workspaceId: $workspaceId,
);
```

## Errors

Every failure is a `Fopost\Sdk\Exception\FopostException` subclass, so one catch covers the lot:

```php
use Fopost\Sdk\Exception\FopostException;
use Fopost\Sdk\Exception\RateLimitException;
use Fopost\Sdk\Exception\ValidationException;

try {
    Fopost::posts()->publish($postId);
} catch (ValidationException $e) {
    report($e);
} catch (RateLimitException $e) {
    // retry after $e->retryAfter seconds
} catch (FopostException $e) {
    report($e);
}
```

## Testing your app

Swap the container binding for a client wired to your own transport, and nothing touches the network:

```php
use Fopost\Sdk\Client;
use Fopost\Sdk\Http\Response;
use Fopost\Sdk\Http\Transport;

$this->app->instance(Client::class, new Client('fp_test', 'https://api.fopost.com', 30.0, 3, $fakeTransport));
```

## Looking for the free self-hosted toolkit?

This package talks to the FoPost Cloud API with a FoPost API key. If you want to publish straight to the
social platforms using your own app credentials, with no FoPost account involved, use
[`fopost/social-laravel`](https://github.com/fopost/fopost-social-laravel) instead. The two families are
separate on purpose and never depend on each other.

## Links

- Documentation: [fopost.com/docs](https://fopost.com/docs)
- API keys: [app.fopost.com/api-keys](https://app.fopost.com/api-keys)
- Support: [fopost.com/contact](https://fopost.com/contact)

## License

MIT. Copyright Porter Bridge, LLC. See [LICENSE](LICENSE).
