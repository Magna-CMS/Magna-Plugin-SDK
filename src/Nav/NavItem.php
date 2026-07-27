<?php

declare(strict_types=1);

namespace Magna\Admin\Nav;

/**
 * Represents a single item within a NavGroup.
 */
final class NavItem
{
    private ?string $requiredPermission = null;

    private function __construct(
        public readonly string $label,
        public readonly ?string $route = null,
        public readonly ?string $resourceClass = null,
    ) {}

    /**
     * A link to a Filament resource. The item label defaults to the resource's
     * class basename and the URL resolves to `{$resourceClass}::getUrl()`.
     */
    public static function resource(string $resourceClass): self
    {
        // Pure-PHP basename so the SDK stays dependency-free (illuminate/support,
        // which provides class_basename(), is not a hard SDK dependency).
        $pos = strrpos($resourceClass, '\\');
        $label = $pos === false ? $resourceClass : substr($resourceClass, $pos + 1);

        return new self(label: $label, resourceClass: $resourceClass);
    }

    /**
     * A link to a named route or page.
     */
    public static function page(string $label, string $route): self
    {
        return new self(label: $label, route: $route);
    }

    /**
     * Restrict this item to users holding the given permission.
     */
    public function can(string $permission): self
    {
        $clone = new self($this->label, $this->route, $this->resourceClass);
        $clone->requiredPermission = $permission;

        return $clone;
    }

    public function getRequiredPermission(): ?string
    {
        return $this->requiredPermission;
    }
}
