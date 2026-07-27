<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Magna\Admin\Nav\NavGroup;
use Magna\Admin\Nav\NavItem;
use PHPUnit\Framework\TestCase;

final class NavTest extends TestCase
{
    public function test_nav_group_defaults_and_make(): void
    {
        $group = NavGroup::make('Blog');

        $this->assertSame('Blog', $group->label);
        $this->assertSame('puzzle-piece', $group->icon);
        $this->assertSame([], $group->getItems());
    }

    public function test_nav_group_items_is_immutable(): void
    {
        $group = NavGroup::make('Blog', 'book-open');
        $withItems = $group->items([NavItem::page('Posts', 'blog.posts')]);

        $this->assertCount(0, $group->getItems(), 'original group must be unchanged');
        $this->assertCount(1, $withItems->getItems());
        $this->assertSame('book-open', $withItems->icon);
    }

    public function test_nav_item_page(): void
    {
        $item = NavItem::page('Posts', 'blog.posts');

        $this->assertSame('Posts', $item->label);
        $this->assertSame('blog.posts', $item->route);
        $this->assertNull($item->resourceClass);
        $this->assertNull($item->getRequiredPermission());
    }

    public function test_nav_item_resource_labels_from_class_basename(): void
    {
        $item = NavItem::resource('Acme\\Blog\\Filament\\PostResource');

        $this->assertSame('PostResource', $item->label);
        $this->assertSame('Acme\\Blog\\Filament\\PostResource', $item->resourceClass);
        $this->assertNull($item->route);
    }

    public function test_nav_item_can_is_immutable(): void
    {
        $item = NavItem::page('Posts', 'blog.posts');
        $restricted = $item->can('blog.posts.view');

        $this->assertNull($item->getRequiredPermission(), 'original item must be unchanged');
        $this->assertSame('blog.posts.view', $restricted->getRequiredPermission());
        $this->assertSame('Posts', $restricted->label);
    }
}
