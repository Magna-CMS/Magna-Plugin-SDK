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
| `RegistersMediaSources` | `mediaSources()` | Files the plugin stores outside the media library |
| `RegistersSiteKitSections` | `siteKitSections()` | Plugin entities that travel in a site-kit bundle |
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

## Files your plugin stores itself

The media library is presented to operators as the one screen that accounts for
every file an installation holds. If your plugin writes uploads anywhere other
than through core's `MediaIngestor`, say so — otherwise the library reports a
number that is quietly wrong, and nobody can tell.

Two acceptable homes for an upload:

1. **The library**, through `Magna\Media\MediaIngestor`. The file appears on
   `/media` like any other. Right for anything an operator would reuse.
2. **Your own disk**, plus `RegistersMediaSources`. Right for files the library
   must never serve — identity documents, contracts, private attachments.

Declaring a source is not exposing it:

```php
final class ContractsMediaSource implements MediaSource
{
    public function key(): string { return 'crm.contracts'; }   // prefix with your plugin
    public function label(): string { return 'Signed contracts'; }
    public function permission(): ?string { return 'crm.contracts.view'; }
    public function isConfidential(): bool { return true; }
    public function count(): int { return Contract::query()->count(); }

    /** @return list<MediaSourceItem> */
    public function items(int $page, int $perPage, string $search = ''): array
    {
        return Contract::query()->latest()->forPage($page, $perPage)->get()
            ->map(fn (Contract $c): MediaSourceItem => new MediaSourceItem(
                id: $c->id,
                name: $c->file_name,
                mimeType: $c->mime_type,
                sizeBytes: $c->size_bytes,
                uploadedAt: $c->created_at,
                ownerLabel: $c->customer_name,
                manageUrl: ContractResource::getUrl('index'),
            ))
            ->all();
    }
}
```

A confidential source renders with no thumbnail, no preview and no storage URL;
the operator follows `manageUrl` into your plugin, where your authorisation runs
and your access log records the read. Never set `thumbnailUrl` on one — a
thumbnail is the file. And `permission()` should not be null for a confidential
source: file names alone (`passport-scan.jpg`) disclose things their owner did
not agree to share.

## Entities that travel in a site kit

`magna:site:export` / `diff` / `sync` promote a site between environments as
one JSON bundle. Out of the box the bundle carries what Magna Pages owns —
pages, templates, menus, patterns, settings. If your plugin owns entities a
page references by handle (a form a form block selects, say), implement
`RegistersSiteKitSections` or every kit using them applies cleanly and renders
a hole the admin must hand-fill.

```php
final class FormsSection implements SiteKitSection
{
    public function key(): string { return 'forms:forms'; } // 'plugin:entity' — the colon is required

    /** @return list<array<string, mixed>> */
    public function export(): array { /* every row, deterministically ordered, keyed by handle */ }

    /** @return array{added: list<string>, changed: list<string>, localOnly: list<string>} */
    public function diff(array $rows): array { /* what apply would change */ }

    public function apply(array $rows, ?string $actorId = null): int { /* upsert by handle, skip malformed rows */ }
}
```

Rules the reader enforces: the key must contain a colon (that namespace is
what keeps plugin sections clear of the kit's own keys), rows reference by
handle/slug/name and never by id (ids are environment facts), and apply runs
inside the kit's single transaction — skip a bad row, throw only when the
whole section cannot apply. A kit section no local plugin registers is
skipped and reported, never fatal.

## Optional integrations

Soft integrations need no contract. Check at runtime whether another plugin is
enabled and adapt — reserve `requires`/`conflict` for hard constraints only.
