# Plugin dependencies

Magna plugins can declare hard relationships to other plugins in `magna.json`.
The SDK resolves these into a validated, deterministic boot order.

## Fields

Both fields are optional objects mapping a `vendor/package` name to a
[semver constraint](https://getcomposer.org/doc/articles/versions.md).

```json
{
    "requires": { "acme/core": "^1.0" },
    "conflict": { "acme/legacy-blog": "*" }
}
```

| Field | Meaning |
| --- | --- |
| `requires` | Hard dependencies. Each must be installed, enabled and version-compatible before this plugin is enabled. Dependencies boot first. |
| `conflict` | Plugins this one cannot run alongside. Enabling both is refused. |

## Boot order

Magna performs a topological sort of the enabled set: a plugin always boots
**after** every plugin it `requires`. Independent plugins keep a stable
alphabetical order, so the same enabled set always boots identically.

## Validation

The graph is validated when a plugin is enabled and again on every boot. Errors
state what failed and how to fix it:

| Situation | Message |
| --- | --- |
| Missing requirement | `Plugin "X" requires "Y" (^1.0), which is not installed or not enabled. Install and enable "Y" first.` |
| Unmet version | `Plugin "X" requires "Y" ^2.0, but version 1.0.0 is enabled. Update "Y" to a version matching ^2.0.` |
| Conflict | `Plugin "X" conflicts with "Y" *, which is enabled … Disable "Y" …` |
| Circular dependency | `Circular plugin dependency detected: A -> B -> A. Break the cycle …` |

Disabling a plugin another enabled plugin still `requires` is refused until the
dependent is disabled.

## Optional integrations

Soft, optional integrations need **no** manifest field. Detect at runtime whether
another plugin is enabled and adapt:

```php
public function boot(): void
{
    if (app(\Magna\Plugins\PluginManager::class)->getEnabled()['acme/analytics'] ?? null) {
        // wire up the optional integration
    }
}
```

`requires` / `conflict` are strictly for hard constraints.

## What does not belong in the manifest

Marketplace/discovery metadata — homepage, repository, funding, keywords — lives
in your `composer.json`, not the runtime `magna.json`. The manifest is limited to
what the CMS needs at runtime to load, order, secure and validate a plugin.

## Testing dependency behaviour

The SDK ships `Magna\Plugins\DependencyResolver` (pure, no framework needed):

```php
$order = (new DependencyResolver)->resolveBootOrder($manifestsKeyedByName);
$resolver->assertCanEnable($candidateManifest, $enabledManifestsKeyedByName);
```

Both throw `Magna\Plugins\Exceptions\DependencyException` with the messages above.
