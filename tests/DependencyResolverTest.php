<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Magna\Plugins\DependencyResolver;
use Magna\Plugins\Exceptions\DependencyException;
use Magna\Plugins\Manifest;
use PHPUnit\Framework\TestCase;

final class DependencyResolverTest extends TestCase
{
    /**
     * @param  array<string, string>  $requires
     * @param  array<string, string>  $conflict
     */
    private function mf(string $name, string $version = '1.0.0', array $requires = [], array $conflict = []): Manifest
    {
        return Manifest::fromArray([
            'name' => $name,
            'displayName' => $name,
            'description' => 'x',
            'version' => $version,
            'author' => 'Acme',
            'license' => 'MIT',
            'compat' => ['magna' => '^1.0'],
            'entry' => 'Acme\\X\\Plugin',
            'permissions' => [],
            'requires' => $requires,
            'conflict' => $conflict,
        ]);
    }

    /**
     * @param  list<Manifest>  $manifests
     * @return array<string, Manifest>
     */
    private function keyed(array $manifests): array
    {
        $map = [];
        foreach ($manifests as $m) {
            $map[$m->name] = $m;
        }

        return $map;
    }

    public function test_it_orders_dependencies_before_dependents(): void
    {
        $set = $this->keyed([
            $this->mf('acme/app', '1.0.0', requires: ['acme/core' => '^1.0']),
            $this->mf('acme/core', '1.2.0'),
        ]);

        $order = (new DependencyResolver)->resolveBootOrder($set);

        $this->assertSame(['acme/core', 'acme/app'], $order);
    }

    public function test_independent_plugins_boot_in_deterministic_alphabetical_order(): void
    {
        $set = $this->keyed([$this->mf('acme/zebra'), $this->mf('acme/alpha'), $this->mf('acme/mid')]);

        $this->assertSame(
            ['acme/alpha', 'acme/mid', 'acme/zebra'],
            (new DependencyResolver)->resolveBootOrder($set),
        );
    }

    public function test_missing_requirement_is_reported(): void
    {
        $set = $this->keyed([$this->mf('acme/app', '1.0.0', requires: ['acme/core' => '^1.0'])]);

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('requires "acme/core"');

        (new DependencyResolver)->resolveBootOrder($set);
    }

    public function test_unsatisfied_version_is_reported(): void
    {
        $set = $this->keyed([
            $this->mf('acme/app', '1.0.0', requires: ['acme/core' => '^2.0']),
            $this->mf('acme/core', '1.0.0'),
        ]);

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('version 1.0.0 is enabled');

        (new DependencyResolver)->resolveBootOrder($set);
    }

    public function test_conflict_is_reported(): void
    {
        $set = $this->keyed([
            $this->mf('acme/a', '1.0.0', conflict: ['acme/b' => '*']),
            $this->mf('acme/b', '1.0.0'),
        ]);

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('conflicts with "acme/b"');

        (new DependencyResolver)->resolveBootOrder($set);
    }

    public function test_circular_dependency_is_reported(): void
    {
        $set = $this->keyed([
            $this->mf('acme/a', '1.0.0', requires: ['acme/b' => '*']),
            $this->mf('acme/b', '1.0.0', requires: ['acme/a' => '*']),
        ]);

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Circular plugin dependency');

        (new DependencyResolver)->resolveBootOrder($set);
    }

    public function test_assert_can_enable_requires_dependency_present(): void
    {
        $resolver = new DependencyResolver;

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Install and enable "acme/core" first');

        $resolver->assertCanEnable(
            $this->mf('acme/app', '1.0.0', requires: ['acme/core' => '^1.0']),
            [],
        );
    }

    public function test_assert_can_enable_detects_reverse_conflict(): void
    {
        $resolver = new DependencyResolver;
        $enabled = $this->keyed([$this->mf('acme/a', '1.0.0', conflict: ['acme/b' => '*'])]);

        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Enabled plugin "acme/a" conflicts');

        $resolver->assertCanEnable($this->mf('acme/b', '1.0.0'), $enabled);
    }

    public function test_assert_can_enable_passes_when_satisfied(): void
    {
        $resolver = new DependencyResolver;
        $enabled = $this->keyed([$this->mf('acme/core', '1.5.0')]);

        $resolver->assertCanEnable($this->mf('acme/app', '1.0.0', requires: ['acme/core' => '^1.0']), $enabled);

        $this->assertTrue(true); // no exception
    }
}
