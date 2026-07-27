# Troubleshooting & FAQ

## Diagnostics

Two commands answer most "why isn't my plugin working" questions:

```bash
php artisan magna:plugin:validate acme/blog   # manifest + dependency graph (static)
php artisan magna:plugin:doctor acme/blog     # runtime health of an enabled plugin
```

`doctor` checks that files are present, the entry class autoloads, the
dependency graph is satisfied, migrations are applied, and the on-disk version
matches what is enabled. See [Developer tooling](/docs/developer-tooling).

## Common problems

**Plugin isn't discovered.**
It must be a Composer package with `"type": "magna-plugin"` and a valid
`magna.json`. In local development it also must be wired into the root
`composer.json` as a path repository. Run `php artisan magna:plugin:list`.

**"Entry class … cannot be autoloaded."**
The `entry` in `magna.json` doesn't match a class Composer can load. Check the
namespace/path and run `composer dump-autoload`.

**Enable fails with a dependency error.**
A `requires` entry is missing, disabled, or version-incompatible. The message
names the plugin and constraint — install/enable/update it, then retry. See
[Plugin dependencies](/docs/plugin-dependencies).

**Migrations didn't run.**
They run on enable. If you added a migration afterwards, `doctor` flags it as
unapplied — run `php artisan migrate`.

**Admin pages/widgets don't appear after enabling.**
The Filament component cache is stale. Magna clears it on enable/disable; if you
changed code manually, run `php artisan filament:clear-cached-components`.

**Routes 404.**
Your package slug may collide with a reserved core slug (routes are refused in
that case) — rename the package. Otherwise confirm the route file is loaded in
`boot()` via `loadRoutesFrom()`.

## FAQ

**Do I need to publish assets/config like a normal Laravel package?**
No. Magna plugins load in place — register views/config in `boot()` with the
[authoring helpers](/docs/authoring-helpers). There is no `vendor:publish` step.

**Can my plugin add an Artisan command?**
Yes — implement [`RegistersCommands`](/docs/capabilities).

**Can one plugin extend another?**
For hard needs, use `requires`. For optional integrations, check at runtime
whether the other plugin is enabled and use its public API.

**Where do secrets/config go?**
Admin-editable settings live in the Settings layer; build-time values in `.env`.
Never in `magna.json`.

**Does disabling a plugin delete its data?**
No. Disable preserves data; only `uninstall --purge` drops declared tables.

## Related

- [Developer tooling](/docs/developer-tooling)
- [Plugin lifecycle](/docs/plugin-lifecycle)

Next: [Publishing](/docs/publishing).
