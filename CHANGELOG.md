# Changelog

All notable changes to the Magna Plugin SDK are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This package follows [Semantic Versioning](https://semver.org).

## [1.7.0] — 2026-10-12

### Added

- `RegistersSiteKitSections`, `SiteKitSection` — a plugin contributes its own
  entities to a site-kit bundle, keyed by a namespaced section key
  (`forms:forms`), so exporting, diffing, and promoting a site carries the
  plugin's content too.

  Until now the kit was a closed list of what Magna Pages itself owns: pages,
  templates, menus, patterns, settings. A site whose contact page uses the
  forms plugin's form block applied cleanly and rendered a hole, because the
  form the block selects by handle did not travel — the kit's "one apply"
  promise quietly became "one apply plus a README of manual steps". Sections
  reference by handle/slug/name (never id — ids are environment facts), apply
  as upserts inside the kit's one transaction, and a kit section no local
  plugin registers is skipped and reported rather than fatal, so an older
  installation can read a newer kit.

## [1.6.0] — 2026-09-26

### Added

- `Plugin::upgrade(string $from, string $to)` — the core tells every enabled
  plugin when it has just been updated. Called once per core update, under
  the new release, after the plugin's own migrations have run; a safe no-op
  by default. Both versions are clean `x.y.z` strings, and a repair
  re-applies the running release, so `$from` and `$to` are then equal.

  Until now a plugin found out about a core update the hard way: something it
  cached against the old core stopped matching, and nothing had told it. The
  hook is where a cached schema, a settings shape or a compatibility shim that
  can now go gets reconciled. Throwing is reported and does not stop the other
  plugins being told. Core 1.4.4 calls it; an older core simply never does.

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
