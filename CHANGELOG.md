# Changelog

All notable changes to the Magna Plugin SDK are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This package follows [Semantic Versioning](https://semver.org).

## [1.5.0] — 2026-08-19

### Added

- `RegistersMediaSources`, `MediaSource`, `MediaSourceItem` — a plugin declares
  files it stores outside the core media library, so the library can account
  for every file on an installation.

  Plugins that keep uploads on their own disk — compliance documents, message
  attachments, controlled documents — were invisible to the media library,
  which is presented to operators as the one screen that shows everything.
  Nothing said the list was partial, and silence reads as "there is nothing".

  Declaring a source does not expose it. A source marked confidential hands
  over names, sizes and owners and no URL to the bytes; the library links back
  to the plugin's own screen, where that plugin's authorisation and access
  logging still run. Identity documents can be counted centrally without
  becoming readable centrally.

## [1.3.0-beta] — Unreleased

First public beta of the standalone Plugin SDK. Targets Magna CMS v1.3.0-beta.

### Public API

- `Plugin` base class — lifecycle (`register`/`boot`/`enable`/`disable`),
  accessors, and dependency-free authoring helpers (`loadRoutesFrom`,
  `loadViewsFrom`, `mergeConfigFrom`, `listen`).
- Capability contracts: `RegistersAdminNavigation`, `RegistersAdminResources`,
  `RegistersDashboardWidgets`, `RegistersSettingsPages`, `RegistersBlocks`,
  `RegistersWebhookEvents`, `RegistersCommands`, `ExtendsEntryForm`,
  `DecoratesDeliveryResponse`, `HandlesPersonalData`.
- `Manifest` + `ManifestValidator` — schema-validated `magna.json` with
  `requires` / `conflict` dependency declarations.
- `DependencyResolver` — deterministic boot order with missing/version/conflict
  and circular-dependency detection.
- `NavGroup` / `NavItem` navigation builders.

### Notes

- The SDK is dependency-light (`illuminate/contracts` + `composer/semver`) and
  static-analyses standalone at PHPStan level 9.
- The public API surface is locked by `PublicApiStabilityTest`; removing or
  renaming a public symbol fails CI.
