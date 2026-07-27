# Plugin developer tooling

Magna ships Artisan commands (run from a Magna app, where the SDK + core are
installed) that remove repetitive plugin work.

## Commands

| Command | Purpose |
| --- | --- |
| `magna:plugin:make {vendor/package}` | Scaffold a complete, validation-passing plugin skeleton (manifest, entry class, routes, README, phpunit, a passing manifest test). |
| `magna:plugin:list` | List discovered plugins with version and status. |
| `magna:plugin:info {vendor/package}` | Show metadata, `requires`/`conflict`, permissions, dependents and install/enabled status. |
| `magna:plugin:validate [name] [--all] [--json] [--strict]` | Validate manifests, dependency graph and cross-plugin collisions. CI-friendly. |
| `magna:plugin:doctor [name]` | Diagnose enabled plugins at runtime: files present, entry class autoloadable, dependencies still satisfied, on-disk vs recorded version drift. |
| `magna:plugin:enable` / `:disable` / `:install` / `:uninstall` | Lifecycle management. |

## validate vs doctor

- **`validate`** is *static* — it reads discovered manifests and is the release/CI
  gate (does this plugin's manifest + declared graph hold up?).
- **`doctor`** is *runtime* — it reads the live `plugins` table and checks that
  what is actually enabled is still healthy (files present, class loadable,
  dependencies satisfied, version not drifted). Reach for it when something that
  worked yesterday misbehaves after a file/version change.

## Validation

`magna:plugin:validate` checks, across the discovered set:

- **manifest compatibility** — each plugin's `compat.magna` against the running core;
- **dependency graph** — missing / unsatisfied `requires`, active `conflict`, and circular dependencies (via the SDK `DependencyResolver`);
- **cross-plugin collisions** — permission keys declared by more than one plugin (a warning).

Exit code is non-zero on any error, so it drops straight into CI. `--strict`
promotes warnings to errors. `--json` prints a machine-readable report:

```json
{
    "ok": true,
    "errors": 0,
    "warnings": 0,
    "plugins": { "acme/blog": { "errors": [], "warnings": [] } }
}
```

## CI example (GitHub Actions)

```yaml
name: plugin
on: [push, pull_request]
jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-interaction --prefer-dist
      # Inside a Magna app that has your plugin wired as a path repository:
      - run: php artisan magna:plugin:validate --strict --json
```

## Inspecting a plugin

```
php artisan magna:plugin:info acme/blog
```

Shows the manifest metadata, dependency maps, declared permissions, which other
plugins depend on it, and whether it is installed/enabled.
