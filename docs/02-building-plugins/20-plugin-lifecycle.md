# Plugin lifecycle

A plugin's entry class extends `Magna\Plugins\Plugin` and overrides only the
hooks it needs. All hooks are safe no-ops by default.

## Hooks

| Hook | When | Use for |
| --- | --- | --- |
| `register()` | once per request bootstrap, before any `boot()` | bind services into the container |
| `boot()` | after every enabled plugin has registered | routes, views, listeners, config merges |
| `enable()` | one-time, when an admin enables the plugin | first-time setup beyond migrations |
| `disable()` | one-time, when an admin disables the plugin | teardown of runtime state (never destroy data) |

Constructor injection gives you `$this->app` (the container), `$this->basePath`
(the plugin root) and `$this->manifest`.

## Discovery

Magna discovers plugins from two sources:

1. `vendor/composer/installed.json` — packages with `"type": "magna-plugin"`.
2. `plugins-dev/{vendor}/{package}/magna.json` — local path repositories, only
   in non-production and only when the package is wired into the root
   `composer.json`.

Discovery is memoised per request.

## Enable / disable / uninstall

- **enable** — validates core compatibility and the dependency graph, runs the
  plugin's migrations, writes the `plugins` record, then registers routes,
  permissions, schemas and content types (in one transaction).
- **disable** — data is preserved; content types are deregistered, the record is
  flagged disabled. Refused if another enabled plugin still `requires` this one.
- **uninstall** — removes the record; `--purge` also drops the tables the
  manifest declares (never a core table).

## Boot order

Enabled plugins boot in dependency order: a plugin always boots after every
plugin it `requires`, with independent plugins in a stable alphabetical order.
See [Plugin dependencies](/docs/plugin-dependencies).

## Failure isolation

A plugin whose files were deleted, or whose `register()`/`boot()` throws, is
auto-disabled with a logged error rather than crashing the CMS — the admin panel
always stays reachable. Security-critical middleware, singletons and audit
listeners are snapshotted before the boot pass and any tampering is reverted.
