# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working in this repository.

## What This Is

`fopost/laravel` — the official Laravel integration for the FoPost Cloud API. It is a thin
wrapper: it binds `Fopost\Sdk\Client` into the Laravel container from `config/fopost.php`, adds
the `Fopost` facade, and publishes the config file. That is the whole package. PHP 8.1+, Laravel
10/11/12, PSR-4 namespace `Fopost\Laravel\`, MIT.

Not to be confused with `fopost/social-laravel` (repo `fopost-social-laravel`), the free
self-hosted toolkit that publishes with the user's own platform credentials. The two families are
separate on purpose and never depend on each other.

## Parent SDK

This package is a THIN WRAPPER over `fopost/sdk` (repo `fopost-php`,
checked out as a sibling at `../fopost-php`).

**Never reimplement API logic, HTTP, retries, models or error handling here** — all of it
belongs in the parent. If something is missing, add it to the parent and depend on it.
This repo only contains framework wiring: configuration, dependency injection, background
jobs, webhook receiving, and framework-idiomatic ergonomics.

When the parent's public surface changes, this repo needs a matching PR.

### Parent dependency (registry status)

`fopost/sdk` is **not on Packagist yet**. `composer.json` declares the normal released
constraint (`"fopost/sdk": "^0.1"`) because that is what ships, but nothing in this repo or in
`.github/workflows/ci.yml` resolves the parent from source, so a clean `composer update` cannot
find it today. Until the parent is published, resolve it with a VCS repository — preferably as a
CI step, not a committed `repositories` block:

```bash
composer config repositories.parent vcs https://github.com/fopost/fopost-php
```

Delete that shim the day `fopost/sdk` lands on Packagist.

## Brand Rules

- The product is **FoPost** (`fopost.com`). Never write "OwlStack" — retired Aug 2026.
- Never write an email address, and never a `mailto:`. Support is https://fopost.com/contact and
  GitHub issues.
- Never name AI providers/models, infrastructure vendors, hosting, or any person. The author is
  the brand / Porter Bridge, LLC.

## Architecture

Four files carry the package:

| File | Role |
| :--- | :--- |
| `src/FopostServiceProvider.php` | Merges + publishes the config, binds `Client::class` as a singleton, aliases it to `fopost`, declares `provides()` |
| `src/Facades/Fopost.php` | Facade over `Client::class`, with `@method` hints for every parent resource |
| `config/fopost.php` | `api_key`, `base_url`, `timeout`, `max_retries`, all from `FOPOST_*` env |
| `composer.json` `extra.laravel` | Auto-discovery of the provider and the `Fopost` alias |

A call flows straight through: `Fopost::posts()->create(...)` resolves the container singleton and
hands off to the parent SDK, which owns the transport. Nothing in this repo touches HTTP.

Transport facts that matter here only because the config sets them: auth is `X-API-Key`, the base
URL defaults to `https://api.fopost.com` and the parent appends `/api/v1` when the configured URL
carries no path, timeout defaults to 30s, and retries default to 3 attempts. A user setting
`FOPOST_API_URL` to a value that already ends in `/api/v1` is left alone — `ServiceProviderTest`
pins both cases.

## Commands

```bash
composer install
./vendor/bin/phpunit                 # the whole suite
./vendor/bin/phpunit --filter test_the_client_is_built_from_config
```

There is no linter or formatter configured in this repo. CI (`.github/workflows/ci.yml`) runs the
suite across PHP 8.1–8.4 × Laravel 10/11/12, excluding PHP 8.1 with Laravel 11 and 12.

## Conventions

- `declare(strict_types=1);` at the top of every PHP file; classes are `final` unless a subclass
  is the point.
- Config is read once in `register()`; never call `env()` outside `config/fopost.php`.
- Comments stay short and explain a "why". No narrated docblocks over obvious code. Public
  docblocks that carry `@method` hints (the facade) are the exception and must stay in sync with
  the parent's resources.
- Type hints on everything, including closure returns.
- Every new config key ships with its `FOPOST_*` env var, a default, and the README table row.

## Testing

`orchestra/testbench` boots a real Laravel app around the package. `tests/TestCase.php` registers
the provider and the alias and sets a dummy key.

- **Nothing hits the network.** `tests/RecordingTransport.php` implements the parent's
  `Transport` interface, records method/url/headers/body, and replays a canned JSON body. Any new
  test that needs a response uses it.
- Cover the wiring, not the parent: the singleton is a singleton, config reaches the client, the
  config file publishes under the `fopost-config` tag, the facade resolves and proxies, and the
  `X-API-Key` header is sent. Resource behaviour is the parent's test suite's job.

## Releasing

There is **no `.github/workflows/release.yml`** in this repo and **no repo secret to configure**.
Composer packages are released by tag:

1. Bump nothing — this package carries no version constant; the tag is the version.
2. `git tag v<version> && git push --tags`.
3. Packagist picks the tag up through its GitHub hook.

`fopost/laravel` is **not on Packagist yet** (nor is its parent `fopost/sdk`), so step 3 is
inert until the package is submitted. The README badges point at a Packagist page that does not
exist yet; that is expected, not a bug to "fix" by removing them.

## Git

Conventional Commits, atomic. Branch `feature/<description>`, merge to `main` via PR.
Never `gh pr create` — push the branch and hand over the compare link.
