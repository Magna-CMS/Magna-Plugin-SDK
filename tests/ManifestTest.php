<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Magna\Plugins\Exceptions\InvalidManifestException;
use Magna\Plugins\Manifest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ManifestTest extends TestCase
{
    private function valid(): array
    {
        return [
            'name' => 'acme/hello',
            'displayName' => 'Hello',
            'description' => 'An example plugin.',
            'version' => '1.0.0',
            'author' => 'Acme',
            'license' => 'MIT',
            'compat' => ['magna' => '^1.0', 'php' => '^8.3'],
            'entry' => 'Acme\Hello\HelloPlugin',
            'permissions' => ['hello.widget.view'],
        ];
    }

    public function test_it_parses_a_valid_manifest(): void
    {
        $manifest = Manifest::fromArray($this->valid());

        $this->assertSame('acme/hello', $manifest->name);
        $this->assertSame('Acme\Hello\HelloPlugin', $manifest->entryClass);
        $this->assertSame(['hello.widget.view'], $manifest->permissions);
    }

    public function test_it_checks_core_compatibility(): void
    {
        $manifest = Manifest::fromArray($this->valid());

        $this->assertTrue($manifest->isCompatibleWith('1.4.0'));
        $this->assertFalse($manifest->isCompatibleWith('2.0.0'));
    }

    public function test_it_rejects_a_bad_name(): void
    {
        $this->expectException(InvalidManifestException::class);

        Manifest::fromArray([...$this->valid(), 'name' => 'NotValid']);
    }

    public function test_it_rejects_a_bad_permission_key(): void
    {
        $this->expectException(InvalidManifestException::class);

        Manifest::fromArray([...$this->valid(), 'permissions' => ['NotDotted']]);
    }

    /**
     * S1-07: a plugin's package name segment becomes its route-prefix slug
     * (PluginManager::loadRoutes() registers under api/v1/{slug}). Core
     * kernel modules boot before plugins, so a plugin using one of these
     * slugs could otherwise permanently shadow the matching core module's
     * routes on the exact same literal path.
     */
    public function test_it_rejects_a_reserved_core_module_slug(): void
    {
        $this->expectException(InvalidManifestException::class);

        Manifest::fromArray([...$this->valid(), 'name' => 'acme/auth']);
    }

    /** @return list<array{0: string}> */
    public static function reservedSlugProvider(): array
    {
        return [
            ['admin'], ['audit'], ['auth'], ['blocks'], ['content'], ['delivery'],
            ['install'], ['management'], ['media'], ['plugins'], ['privacy'],
            ['settings'], ['users'], ['webhooks'],
        ];
    }

    #[DataProvider('reservedSlugProvider')]
    public function test_it_rejects_every_reserved_slug(string $slug): void
    {
        $this->expectException(InvalidManifestException::class);

        Manifest::fromArray([...$this->valid(), 'name' => "acme/{$slug}"]);
    }

    public function test_it_allows_marketplace_as_a_package_slug(): void
    {
        // Deliberately NOT reserved — the core Marketplace library registers
        // no routes of its own; reserving it would break the real
        // production magna-cms/marketplace plugin's own package name.
        $manifest = Manifest::fromArray([...$this->valid(), 'name' => 'magna-cms/marketplace']);

        $this->assertSame('magna-cms/marketplace', $manifest->name);
    }

    public function test_it_parses_requires_and_conflict_maps(): void
    {
        $manifest = Manifest::fromArray([
            ...$this->valid(),
            'requires' => ['acme/core' => '^1.0'],
            'conflict' => ['acme/legacy' => '<2.0'],
        ]);

        $this->assertSame(['acme/core' => '^1.0'], $manifest->requires);
        $this->assertSame(['acme/legacy' => '<2.0'], $manifest->conflict);
        $this->assertSame(['acme/core' => '^1.0'], $manifest->toArray()['requires']);
    }

    public function test_requires_and_conflict_default_to_empty(): void
    {
        $manifest = Manifest::fromArray($this->valid());

        $this->assertSame([], $manifest->requires);
        $this->assertSame([], $manifest->conflict);
    }

    public function test_it_rejects_an_invalid_dependency_name(): void
    {
        $this->expectException(InvalidManifestException::class);
        $this->expectExceptionMessage('invalid plugin name');

        Manifest::fromArray([...$this->valid(), 'requires' => ['NotAVendorPackage' => '^1.0']]);
    }

    public function test_it_rejects_an_invalid_version_constraint(): void
    {
        $this->expectException(InvalidManifestException::class);
        $this->expectExceptionMessage('not a valid semver constraint');

        Manifest::fromArray([...$this->valid(), 'requires' => ['acme/core' => 'not-a-constraint']]);
    }

    public function test_it_rejects_a_self_dependency(): void
    {
        $this->expectException(InvalidManifestException::class);
        $this->expectExceptionMessage('cannot list itself');

        Manifest::fromArray([...$this->valid(), 'requires' => ['acme/hello' => '^1.0']]);
    }
}
