<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Magna\Plugins\Exceptions\InvalidManifestException;
use Magna\Plugins\Manifest;
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
}
