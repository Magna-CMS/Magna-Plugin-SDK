# Authoring helpers

`Plugin` provides thin, explicitly-named helpers so `boot()` reads as intent
rather than container plumbing. All paths are relative to your plugin root, and
every helper is dependency-free (each resolves a Laravel service through the
container by its contract).

```php
public function boot(): void
{
    $this->loadViewsFrom('resources/views', 'blog');          // view('blog::post')
    $this->loadRoutesFrom('routes/web.php', 'web');           // middleware group
    $this->loadRoutesFrom('routes/api.php', 'api', 'v1/blog'); // + URL prefix
    $this->mergeConfigFrom('config/blog.php', 'blog');        // config('blog.*')
    $this->listen(PostPublished::class, NotifySubscribers::class);
}
```

| Helper | Does |
| --- | --- |
| `loadViewsFrom($path, $namespace)` | Register a Blade view namespace |
| `loadRoutesFrom($file, $middleware = 'web', $prefix = null)` | Load a route file under a middleware group / prefix |
| `mergeConfigFrom($file, $key)` | Merge plugin config defaults (existing values win) |
| `listen($event, $listener)` | Register an event listener |
| `routesPath($file = 'api.php')` | Absolute path to a route file |

## What is automatic

- **Migrations** in `database/migrations/` are discovered and run on enable — no
  helper needed.
- **Permissions** declared in `magna.json` register automatically.

## What stays plain Laravel

Translation loading and console-command registration are exposed by Laravel only
on concrete classes, so they are not sugared here (that would add a dependency
the SDK deliberately avoids). Register console commands via the
[`RegistersCommands`](/docs/capabilities) contract; load translations with
`$this->app` directly if you need them.
