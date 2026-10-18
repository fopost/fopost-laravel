# Changelog

All notable changes to `fopost/laravel` are listed here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Changed

- `fopost/sdk` is now required at `^0.4`.

### Added

- `Fopost::queue()`, `Fopost::connectSessions()`, `Fopost::webhooks()`, `Fopost::automations()` and `Fopost::logs()` surface the parent SDK's posting queue, hosted connect links, webhooks, automations and request log resources.

## [0.2.0] - 2026-10-18

### Changed

- `fopost/sdk` is now required at `^0.3` from Packagist; the development branch pin and its VCS repository entry are gone.
- PHP 8.2 and Laravel 12 are the supported floor; Laravel 10 and 11 are no longer tested or supported.
- README examples use the `fp_` API key prefix and point at `fopost.com/dashboard`.

### Added

- `Fopost::inbox()`, `Fopost::ads()` and `Fopost::media()` surface the parent SDK's inbox, ads and direct media upload resources.
- `Fopost::contacts()`, `Fopost::activity()`, `Fopost::broadcasts()`, `Fopost::sequences()`, `Fopost::whatsapp()` and `Fopost::googleBusiness()` surface the matching parent resources.
- The Meta ads depth methods (campaign tree, creatives, insights and the leads feed) are available through `Fopost::ads()` with `fopost/sdk` 0.3.
- `Fopost::blog()` surfaces the parent SDK's blog resource.
- `Route::fopostArticles($uri)` mounts a receiver for the `article.published` webhook: it verifies the
  signature through `fopost/sdk`, answers the `article.test` ping, and hands the typed article to
  `Fopost::onArticlePublished()` or the `ArticlePublished` event.
- `FOPOST_WEBSITE_SECRET` (`fopost.website_secret`) holds the secret those deliveries are signed with.
