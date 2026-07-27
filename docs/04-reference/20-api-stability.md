# API stability & deprecation policy

This is the contract between the Magna Plugin SDK and the plugins built on it.
It defines what "public API" means, what will and will not break, and how change
is communicated. It applies from SDK 1.0 onward.

## What is public API

Everything under these namespaces, unless a symbol is marked `@internal`:

- `Magna\Plugins` — `Plugin`, `Manifest`, `ManifestValidator`, `DependencyResolver`
- `Magna\Contracts\*` — the capability contracts
- `Magna\Admin\Nav` — `NavGroup`, `NavItem`
- `Magna\Plugins\Exceptions\*`
- the `magna.json` manifest schema (`schema/magna.schema.json`)

Anything not listed, and anything tagged `@internal`, is implementation detail
and may change at any time.

## Semantic Versioning

The SDK follows [SemVer 2.0.0](https://semver.org). Given `MAJOR.MINOR.PATCH`:

- **PATCH** — bug fixes, no signature changes.
- **MINOR** — backwards-compatible additions (new optional manifest fields, new
  contracts, new `Plugin` helpers, new optional method parameters).
- **MAJOR** — backwards-incompatible changes to public API.

A plugin declares the core it supports with `compat.magna` in `magna.json`; the
SDK version tracks the core it ships with.

## Backwards compatibility

Within a major version the SDK will **not**:

- remove or rename a public class, method, or constant;
- add a required parameter to, or narrow a parameter type of, a public method;
- widen a return type or change it incompatibly;
- add a required manifest field or tighten validation of an existing one;
- change the meaning of an existing manifest field.

The SDK **may**, in a minor release:

- add classes, contracts, methods, and optional parameters;
- add optional manifest fields;
- relax validation;
- add new capability contracts (a plugin opts in by implementing them).

New capability contracts are always additive: not implementing one is always valid.

## Deprecation lifecycle

1. **Announce** — the symbol is marked `@deprecated` in a MINOR release with the
   replacement named in the message, and noted in the changelog.
2. **Coexist** — the deprecated symbol keeps working, unchanged, for the rest of
   the current major version. Deprecations are never removed in a minor/patch.
3. **Remove** — only in the next MAJOR release.

Minimum one full major-version cycle from deprecation to removal.

## Breaking-change policy

Breaking changes ship only in a MAJOR release and only with:

- an entry in `UPGRADING.md` describing the change and the migration step;
- the deprecation path above already served where a bridge was possible.

Breaking changes are batched into a major; they are never trickled into minors.

## Compatibility guarantees

- **Plugin → SDK**: a plugin valid against SDK `1.x` stays valid for every later
  `1.y` (`y ≥ x`). Upgrading the SDK within a major never requires plugin changes.
- **SDK → Core**: the SDK version corresponds to the core it targets via
  `compat.magna`. Core honours the same SemVer contract for the runtime services
  the SDK's helpers resolve (view, config, router, events dispatcher).
- **Manifest**: the schema only gains optional fields within a major. An existing
  `magna.json` never becomes invalid due to an SDK minor/patch upgrade.

## Migration expectations

- **MINOR/PATCH** — drop-in. `composer update`, no code changes.
- **MAJOR** — read `UPGRADING.md`; apply the listed steps. Deprecations flagged
  during the previous major are your migration checklist.
- Run `php artisan magna:plugin:validate --strict` after any upgrade to confirm
  your plugin still satisfies the manifest and dependency contracts.
