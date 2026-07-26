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

### Fixed
- Pin `eslint-plugin-compat` (via `overrides`) and `jsdom` to the newest releases that still support Node.js 12, fixing `npm test` on the MW 1.35 / PHP 7.4 CI matrix row, whose docker image ships Node 12.22.12.

[Unreleased]: https://github.com/gesinn-it/Matomo/compare/main...HEAD
