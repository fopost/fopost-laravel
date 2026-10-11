# Changelog

All notable changes to `fopost/laravel` are listed here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- `Fopost::blog()` surfaces the parent SDK's blog resource.
- `Route::fopostArticles($uri)` mounts a receiver for the `article.published` webhook: it verifies the
  signature through `fopost/sdk`, answers the `article.test` ping, and hands the typed article to
  `Fopost::onArticlePublished()` or the `ArticlePublished` event.
- `FOPOST_WEBSITE_SECRET` (`fopost.website_secret`) holds the secret those deliveries are signed with.
