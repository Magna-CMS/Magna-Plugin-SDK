# Creating a plugin

The fastest way to start is the generator, which emits a complete,
validation-passing skeleton (manifest, entry class, routes, README, phpunit, and
a passing manifest test):

```bash
php artisan magna:plugin:make acme/blog
```

## Anatomy of a plugin

A plugin is a Composer package with `"type": "magna-plugin"`, a `magna.json`
manifest, and an entry class extending `Magna\Plugins\Plugin`:

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
            NavItem::page('Posts', 'filament.admin.resources.blog-posts.index'),
        ]);
    }
}
```

`magna.json`:

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
    "permissions": ["blog.posts.view"]
}
```

The manifest is validated on load — check it any time with
`php artisan magna:plugin:validate acme/blog`.

## Enable it

```bash
php artisan magna:plugin:enable acme/blog
```

Migrations under `database/migrations/` run automatically, permissions register,
routes mount, and any [capability contracts](/docs/capabilities) you implement
are wired in.

## Where to go next

- [Plugin manifest](/docs/plugin-manifest) — every field explained
- [Plugin lifecycle](/docs/plugin-lifecycle) — the hooks and boot order
- [Capabilities](/docs/capabilities) — everything a plugin can extend
- [Authoring helpers](/docs/authoring-helpers) — the `boot()` helpers used above
- [Testing](/docs/plugin-testing) — prove your plugin works
