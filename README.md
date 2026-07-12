# Magna Plugin SDK

The official SDK for building [Magna CMS](https://github.com/jish-44/Magna) plugins.

It contains the **stable, versioned public API** a plugin compiles against — the
extension contracts, the `Plugin` base class, and the `magna.json` manifest spec —
so you can build a plugin in your own repository without depending on Magna's core.

## Install

```bash
composer require magna/plugin-sdk
```

## Quick start

A plugin is a Composer package with `"type": "magna-plugin"`, a `magna.json`
manifest, and an entry class that extends `Magna\Plugins\Plugin`:

```php
use Magna\Plugins\Plugin;
use Magna\Contracts\RegistersDashboardWidgets;

class HelloPlugin extends Plugin implements RegistersDashboardWidgets
{
    public function boot(): void
    {
        // load routes, views, listeners…
    }

    public function dashboardWidgets(): array
    {
        return [\Acme\Hello\Widgets\HelloWidget::class];
    }
}
```

`magna.json`:

```json
{
    "name": "acme/hello",
    "displayName": "Hello",
    "description": "An example plugin.",
    "version": "1.0.0",
    "author": "Acme",
    "license": "MIT",
    "compat": { "magna": "^1.0", "php": "^8.3" },
    "entry": "Acme\\Hello\\HelloPlugin",
    "permissions": []
}
```

The manifest is validated against [`schema/magna.schema.json`](schema/magna.schema.json).

## Extension contracts

Implement any of these interfaces on your entry class to hook into the CMS. The
core calls the matching method at the right point in the lifecycle:

| Contract | Method | Provides |
| --- | --- | --- |
| `RegistersAdminResources` | `adminResources()` | Filament resources (CRUD screens) |
| `RegistersAdminNavigation` | `adminNavigation()` | A sidebar nav group |
| `RegistersDashboardWidgets` | `dashboardWidgets()` | Admin dashboard widgets |
| `RegistersSettingsPages` | `settingsPages()` | Pages under Settings |
| `RegistersBlocks` | `blockPaths()` | Block definitions |
| `RegistersWebhookEvents` | `webhookEvents()` | Custom webhook event keys |
| `ExtendsEntryForm` | `entryFormExtensions()` | Extra fields on a content type form |
| `FiltersApiQuery` | `filterApiQuery()` | Scope the delivery API query |

## Versioning

This package is semantically versioned. Everything exported here is a public
contract: within a major version, existing signatures will not break. Your
plugin declares which core it supports via `compat.magna` in `magna.json`.

## License

MIT.
