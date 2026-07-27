# Changelog

All notable changes to the Magna Plugin SDK are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This package follows [Semantic Versioning](https://semver.org).

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
