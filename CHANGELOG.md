# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/) and
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- Initial repository scaffold: extension skeleton, PHPCS/Phan/PHPUnit tooling, docker-compose-ci integration, DOCSMP-based documentation build.
- Add `$wgMatomoURL`, `$wgMatomoIDSite`, `$wgMatomoProtocol` configuration, delivered to the `ext.matomo.tracker` ResourceLoader module via a `packageFiles` callback [`b54147f`](https://github.com/gesinn-it/Matomo/commit/b54147f) ([#1](https://github.com/gesinn-it/Matomo/issues/1))
- Implement the `ext.matomo.tracker` ResourceLoader module: enqueues the Matomo `_paq` pageview tracking commands and loads `matomo.js` from the configured Matomo instance; module is now loaded on every page via `BeforePageDisplay`. Adds QUnit test tooling (`node-qunit`) with coverage for the tracking logic. ([#2](https://github.com/gesinn-it/Matomo/issues/2))
- Add `$wgMatomoIgnoreGroups` configuration (default: `[ 'bot', 'sysop' ]`) to opt out pageview tracking for users in the listed groups; no user-ID tracking or PII involved, only inclusion/exclusion from the anonymous pageview count. [`d03876e`](https://github.com/gesinn-it/Matomo/commit/d03876e) ([#3](https://github.com/gesinn-it/Matomo/issues/3))
- Add site search tracking: hook `SpecialSearchResults`/`SpecialSearchSetupEngine` to capture the search term, result count, and search profile (category) on Special:Search, and pass it to the `ext.matomo.tracker` module, which pushes `trackSiteSearch` instead of `trackPageView` for search requests. ([#4](https://github.com/gesinn-it/Matomo/issues/4))
- Add `$wgMatomoCustomJS` configuration (default: `[]`): a list of arbitrary `_paq` commands, delivered via the `ext.matomo.tracker` ResourceLoader module and pushed onto `window._paq` after the core tracking commands, letting admins extend tracking (e.g. custom variables or events) without patching the extension or adding inline HTML. ([#5](https://github.com/gesinn-it/Matomo/issues/5))

### Fixed
- Pin `eslint-plugin-compat` (via `overrides`) and `jsdom` to the newest releases that still support Node.js 12, fixing `npm test` on the MW 1.35 / PHP 7.4 CI matrix row, whose docker image ships Node 12.22.12.
- Correct the `getTrackerConfig` PHPDoc: the ResourceLoader context class moved from the global `ResourceLoaderContext` to `MediaWiki\ResourceLoader\Context` in MW 1.39, not 1.36 as previously documented. An audit of all MediaWiki APIs used in `src/Hooks.php` across the 1.35/1.39/1.43 CI matrix found no other cross-version differences requiring a `version_compare()` guard. ([#6](https://github.com/gesinn-it/Matomo/issues/6))

### Docs
- Add a "Configuration" section to `README-source.adoc` documenting all `$wgMatomo*` variables (`MatomoURL`, `MatomoIDSite`, `MatomoProtocol`, `MatomoIgnoreGroups`, `MatomoCustomJS`) with their defaults and behavior. ([#7](https://github.com/gesinn-it/Matomo/issues/7))

[Unreleased]: https://github.com/gesinn-it/Matomo/compare/main...HEAD
