# Plugin manifest

Every plugin ships a `magna.json` at its root. It is the runtime contract Magna
uses to load, order, secure and validate the plugin. It is validated against
[`schema/magna.schema.json`](https://github.com/) by `ManifestValidator`.

## Fields

| Field | Required | Purpose |
| --- | --- | --- |
| `name` | yes | `vendor/package` (lowercase; hyphens/underscores allowed). Unique plugin id and route slug. |
| `displayName` | yes | Human-readable name shown in the admin. |
| `description` | yes | One-line summary. |
| `version` | yes | SemVer version string, e.g. `1.2.0`. |
| `author` | yes | Author or organisation. |
| `license` | yes | SPDX license id, e.g. `MIT`. |
| `compat.magna` | yes | SemVer constraint of the core versions supported, e.g. `^1.0`. |
| `compat.php` | no | PHP constraint, e.g. `^8.3` (defaults to `^8.3`). |
| `entry` | yes | Fully-qualified entry class extending `Magna\Plugins\Plugin`. |
| `permissions` | yes | Dot-separated permission keys, e.g. `blog.posts.view`. May be empty. |
| `provides` | no | Capability hints surfaced in the admin UI. |
| `uninstall` | no | What to drop on `uninstall --purge` (`tables`, `contentTypes`). |
| `icon` | no | Relative path to a square icon (svg/png/jpg/webp) inside the plugin. |
| `requires` | no | Hard dependencies: plugin name → SemVer constraint. See [dependencies](/docs/plugin-dependencies). |
| `conflict` | no | Plugins this one cannot run alongside: name → SemVer constraint. |

## Reserved names

The package slug (the part after `/`) may not be a core module slug (`admin`,
`auth`, `content`, `media`, `settings`, …) — those are reserved so a plugin can
never shadow a core route. `ManifestValidator` rejects them with a clear message.

## What does NOT belong here

Marketplace/discovery metadata — homepage, repository, funding, keywords,
category — lives in your `composer.json`. The manifest is limited to what the CMS
needs at runtime. Keeping the two separated is a deliberate boundary.

## Example

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
    "permissions": ["blog.posts.view", "blog.posts.manage"],
    "requires": { "acme/core": "^1.0" },
    "uninstall": { "tables": ["blog_posts"], "contentTypes": ["post"] }
}
```

Validate any manifest with `php artisan magna:plugin:validate acme/blog`.
