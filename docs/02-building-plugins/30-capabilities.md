# Capabilities (extension contracts)

Implement any of these interfaces on your entry class to hook into the CMS. Core
calls the matching method at the right point in the lifecycle. Not implementing a
contract is always valid — capabilities are opt-in and additive.

| Contract | Method | Provides |
| --- | --- | --- |
| `RegistersAdminResources` | `adminResources()` | Filament resources (CRUD screens) |
| `RegistersAdminNavigation` | `adminNavigation()` | A sidebar nav group |
| `RegistersDashboardWidgets` | `dashboardWidgets()` | Admin dashboard widgets |
| `RegistersSettingsPages` | `settingsPages()` | Pages under Settings |
| `RegistersBlocks` | `blocks()` | Block definitions |
| `RegistersWebhookEvents` | `webhookEvents()` | Custom webhook event keys |
| `RegistersCommands` | `commands()` | Artisan console commands |
| `ExtendsEntryForm` | `entryFormExtensions()` | Extra fields on a content-type form |
| `DecoratesDeliveryResponse` | `decorateDeliveryEntry()` | Inject data into delivery API entries |
| `HandlesPersonalData` | `exportPersonalData()`, `erasePersonalData()` | GDPR export/erase hooks |

## Example

```php
use Magna\Contracts\RegistersCommands;
use Magna\Contracts\RegistersDashboardWidgets;
use Magna\Plugins\Plugin;

class BlogPlugin extends Plugin implements RegistersDashboardWidgets, RegistersCommands
{
    /** @return list<class-string> */
    public function dashboardWidgets(): array
    {
        return [\Acme\Blog\Widgets\LatestPosts::class];
    }

    /** @return list<class-string> */
    public function commands(): array
    {
        return [\Acme\Blog\Commands\ImportPosts::class];
    }
}
```

## Navigation

`RegistersAdminNavigation` returns a `NavGroup` of `NavItem`s:

```php
use Magna\Admin\Nav\NavGroup;
use Magna\Admin\Nav\NavItem;

public function adminNavigation(): NavGroup
{
    return NavGroup::make('Blog', 'book-open')->items([
        NavItem::page('Posts', 'filament.admin.resources.blog-posts.index')->can('blog.posts.view'),
        NavItem::resource(\Acme\Blog\Filament\CategoryResource::class),
    ]);
}
```

## Optional integrations

Soft integrations need no contract. Check at runtime whether another plugin is
enabled and adapt — reserve `requires`/`conflict` for hard constraints only.
