# Testing your plugin

Magna Core ships `Magna\Testing\PluginTestCase` (it boots a full test app, so it
lives in core, not the thin SDK). Extend it, name your plugin, and assert against
a real booted plugin — no setup boilerplate.

## Feature tests

```php
use Magna\Testing\PluginTestCase;

final class BlogPluginTest extends PluginTestCase
{
    protected string $plugin = 'acme/blog'; // enabled before each test

    public function test_it_boots_with_routes_and_permissions(): void
    {
        $this->assertPluginEnabled('acme/blog');
        $this->assertPluginBooted('acme/blog');
        $this->assertRouteRegistered('api/v1/blog/posts');
        $this->assertPermissionRegistered('blog.posts.view');
    }
}
```

## Assertions

| Assertion | Checks |
| --- | --- |
| `assertPluginEnabled($name)` | an enabled record exists |
| `assertPluginDisabled($name)` | exists but disabled |
| `assertPluginBooted($name)` | live in the current request |
| `assertPermissionRegistered($key)` | permission is in the registry |
| `assertRouteRegistered($uri)` | a route for the URI is registered |

Drive lifecycle mid-test with `enablePlugin()` / `disablePlugin()`.

## Unit tests

For pure logic — manifest parsing, nav builders, dependency resolution — you need
nothing from core; construct the SDK value objects directly:

```php
use Magna\Plugins\Manifest;
use Magna\Plugins\DependencyResolver;

$manifest = Manifest::fromArray([...]);
$order = (new DependencyResolver)->resolveBootOrder($manifestsKeyedByName);
```

## CI

Run the manifest + dependency + collision checks headlessly:

```bash
php artisan magna:plugin:validate --strict --json
```

See [Developer tooling](/docs/developer-tooling) for the full command set.
