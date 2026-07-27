# Magna Plugin SDK

<p>
  <img src="https://img.shields.io/badge/version-v1.3.0--beta-orange?style=flat-square" alt="v1.3.0-beta">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/license-MIT-blue?style=flat-square" alt="MIT License">
</p>

The official SDK for building [Magna CMS](https://github.com/Magna-CMS/Magna)
plugins. It is the **stable, versioned public API** a plugin compiles against —
the extension contracts, the `Plugin` base class, and the `magna.json` manifest
spec — so you can build and test a plugin in your own repository without
depending on Magna's core internals.

If you know Laravel, you already know most of what you need.

## Features

- **Dependency-light** — requires only `illuminate/contracts` and
  `composer/semver`; installs and static-analyses standalone.
- **Typed capability contracts** — opt into admin resources, navigation,
  widgets, settings pages, blocks, webhook events, console commands, delivery
  decoration and GDPR hooks by implementing an interface.
- **A thin `Plugin` base class** with a simple lifecycle and boilerplate-free
  authoring helpers (`loadViewsFrom`, `loadRoutesFrom`, `mergeConfigFrom`,
  `listen`).
- **Manifest + validator** — a schema-validated `magna.json`, with a
  first-class plugin **dependency system** (`requires` / `conflict`, deterministic
  boot order, cycle detection).
- **Semantic-versioned** public API with a documented stability policy.

## Requirements

- PHP 8.3 or newer
- Magna CMS core at runtime (a plugin runs inside a Magna installation)

## Installation

```bash
composer require magna-cms/plugin-sdk
```

## Quick start

Scaffold a complete, validation-passing plugin with the core generator (run from
a Magna app):

```bash
php artisan magna:plugin:make acme/blog
```

Or write one by hand. A plugin is a Composer package with
`"type": "magna-plugin"`, a `magna.json` manifest, and an entry class extending
`Magna\Plugins\Plugin`:

```php
use Magna\Admin\Nav\NavGroup;
use Magna\Admin\Nav\NavItem;
use Magna\Contracts\RegistersAdminNavigation;
use Magna\Plugins\Plugin;

class BlogPlugin extends Plugin implements RegistersAdminNavigation
{
    public function boot(): void
    {
        $this->loadViewsFrom('resources/views', 'blog');
        $this->loadRoutesFrom('routes/web.php', 'web');
    }

    public function adminNavigation(): NavGroup
    {
        return NavGroup::make('Blog', 'book-open')->items([
            NavItem::page('Posts', 'filament.admin.resources.blog-posts.index')
                ->can('blog.posts.view'),
        ]);
    }
}
```

Then enable it:

```bash
php artisan magna:plugin:enable acme/blog
```

Migrations under `database/migrations/` run automatically, permissions register,
routes mount, and your capability contracts are wired in.

## Plugin structure

```
acme-blog/
├── magna.json                 # manifest (required)
├── composer.json              # "type": "magna-plugin"
├── src/
│   └── BlogPlugin.php         # entry class extends Magna\Plugins\Plugin
├── routes/
│   ├── web.php
│   └── api.php
├── resources/views/
├── database/migrations/       # run automatically on enable
├── schemas/                   # optional content-type JSON schemas
└── tests/
```

## `magna.json`

```json
{
    "name": "acme/blog",
    "displayName": "Blog",
    "description": "A simple blog for Magna.",
    "version": "1.0.0",
    "author": "Acme",
    "license": "MIT",
    "compat": { "magna": "^1.0", "php": "^8.3" },
    "entry": "Acme\\Blog\\BlogPlugin",
    "permissions": ["blog.posts.view"],
    "requires": { "acme/core": "^1.0" }
}
```

Validated against [`schema/magna.schema.json`](schema/magna.schema.json) and by
`ManifestValidator`. `requires` / `conflict` declare plugin dependencies — see
[docs/02-building-plugins/50-plugin-dependencies.md](docs/02-building-plugins/50-plugin-dependencies.md).

## Available capabilities

Implement any of these interfaces on your entry class:

| Contract | Method | Provides |
| --- | --- | --- |
| `RegistersAdminResources` | `adminResources()` | Filament resources (CRUD screens) |
| `RegistersAdminNavigation` | `adminNavigation()` | A sidebar nav group |
| `RegistersDashboardWidgets` | `dashboardWidgets()` | Admin dashboard widgets |
| `RegistersSettingsPages` | `settingsPages()` | Pages under Settings |
| `RegistersBlocks` | `blocks()` | Block editor definitions |
| `RegistersWebhookEvents` | `webhookEvents()` | Custom webhook event keys |
| `RegistersCommands` | `commands()` | Artisan console commands |
| `ExtendsEntryForm` | `entryFormExtensions()` | Extra fields on a content-type form |
| `DecoratesDeliveryResponse` | `decorateDeliveryEntry()` | Inject data into delivery API entries |
| `HandlesPersonalData` | `exportPersonalData()`, `erasePersonalData()` | GDPR export/erase hooks |

Capabilities are opt-in and additive — not implementing one is always valid.

## Plugin lifecycle

`Plugin` exposes four hooks, all safe no-ops by default; override only what you
need:

| Hook | When |
| --- | --- |
| `register()` | bind services (before any `boot()`) |
| `boot()` | routes, views, listeners, config merges |
| `enable()` | one-time, on admin enable |
| `disable()` | one-time, on admin disable (never destroy data) |

Enabled plugins boot in dependency order; a plugin whose files vanish or whose
boot throws is auto-disabled with a logged error rather than crashing the CMS.

## Publishing

A plugin is an ordinary Composer package. Publish to Packagist (or a private
Composer repo); users install with `composer require vendor/package` and enable
with `php artisan magna:plugin:enable vendor/package`. Follow SemVer for your
plugin's `version` and declare the core you support with `compat.magna`.

## Testing

Magna Core ships `Magna\Testing\PluginTestCase` (it boots a full app, so it
lives in core). Extend it, name your plugin, and assert against a real booted
plugin:

```php
use Magna\Testing\PluginTestCase;

final class BlogPluginTest extends PluginTestCase
{
    protected string $plugin = 'acme/blog';

    public function test_it_boots_with_routes_and_permissions(): void
    {
        $this->assertPluginEnabled('acme/blog');
        $this->assertRouteRegistered('api/v1/blog/posts');
        $this->assertPermissionRegistered('blog.posts.view');
    }
}
```

For pure unit tests (manifest parsing, dependency resolution), construct the SDK
value objects directly — no core needed.

Validate a plugin headlessly in CI:

```bash
php artisan magna:plugin:validate acme/blog --strict --json
```

## Best practices

- Keep the entry class thin; put logic in injected services.
- Namespace everything (views `blog::`, config `blog.*`, permissions
  `blog.*`, tables `blog_*`).
- Depend on SDK contracts, never core implementation classes.
- Make `disable()` reversible; destroy data only on `uninstall --purge`.

More in [docs/03-guides/10-best-practices.md](docs/03-guides/10-best-practices.md).

## Security

A plugin runs with full application access — treat it like core code: authorize
every action, validate all input, and never re-alias core middleware or rebind
security singletons (Magna detects and reverts such tampering on every boot).
Report vulnerabilities via the Magna CMS
[security process](https://github.com/Magna-CMS/Magna/security/advisories/new),
not a public issue. See [SECURITY.md](SECURITY.md).

## Version compatibility

Your plugin declares the core it supports with `compat.magna` in `magna.json`
(e.g. `^1.0`). Magna refuses to enable a plugin whose constraint the running
core doesn't satisfy. This SDK release targets **Magna CMS v1.3.0-beta**.

## API stability & Semantic Versioning

This package follows [Semantic Versioning](https://semver.org). Within a major
version, existing public signatures will not break, deprecations survive a full
major before removal, and a valid `magna.json` never becomes invalid from a
minor/patch upgrade. The full policy — public-API surface, deprecation lifecycle,
breaking-change and migration rules — is in
[docs/04-reference/20-api-stability.md](docs/04-reference/20-api-stability.md).

## Documentation

The complete developer manual (canonical Markdown) lives in [`docs/`](docs):
getting started, manifest, lifecycle, capabilities, authoring helpers,
dependencies, testing, tooling, performance and the stability policy. It renders
as the official Magna Docs site.

## License

MIT © Magna CMS. See [LICENSE](LICENSE).
