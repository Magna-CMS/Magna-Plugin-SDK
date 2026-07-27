<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Locks the SDK's public API surface — the contracts, base class and value
 * objects every third-party plugin compiles against. Presence-based on purpose:
 * ADDING a contract or method is a backwards-compatible minor change and must
 * not fail here, but REMOVING or RENAMING one is a breaking change (major only,
 * per docs/api-stability) and must fail CI so it can never happen by accident.
 */
final class PublicApiStabilityTest extends TestCase
{
    /**
     * @return array<string, list<string>>
     */
    private function publicContracts(): array
    {
        return [
            'Magna\Contracts\RegistersAdminNavigation' => ['adminNavigation'],
            'Magna\Contracts\RegistersAdminResources' => ['adminResources'],
            'Magna\Contracts\RegistersBlocks' => ['blocks'],
            'Magna\Contracts\RegistersDashboardWidgets' => ['dashboardWidgets'],
            'Magna\Contracts\RegistersSettingsPages' => ['settingsPages'],
            'Magna\Contracts\RegistersWebhookEvents' => ['webhookEvents'],
            'Magna\Contracts\RegistersCommands' => ['commands'],
            'Magna\Contracts\ExtendsEntryForm' => ['entryFormExtensions'],
            'Magna\Contracts\DecoratesDeliveryResponse' => ['decorateDeliveryEntry'],
            'Magna\Contracts\HandlesPersonalData' => ['exportPersonalData', 'erasePersonalData'],
        ];
    }

    public function test_public_capability_contracts_remain_stable(): void
    {
        foreach ($this->publicContracts() as $interface => $methods) {
            $this->assertTrue(interface_exists($interface), "Public contract {$interface} must not be removed or renamed.");

            foreach ($methods as $method) {
                $this->assertTrue(
                    method_exists($interface, $method),
                    "Public contract method {$interface}::{$method}() must not be removed or renamed.",
                );
            }
        }
    }

    public function test_plugin_base_lifecycle_is_stable(): void
    {
        // Lifecycle hooks + accessors every plugin relies on. Removing any breaks
        // every plugin. (Authoring helpers loadRoutesFrom/loadViewsFrom/etc are
        // protected; they're covered by PluginTest.)
        foreach (['register', 'boot', 'enable', 'disable', 'getManifest', 'getBasePath'] as $method) {
            $this->assertTrue(
                method_exists('Magna\Plugins\Plugin', $method),
                "Plugin::{$method}() must not be removed or renamed.",
            );
        }
    }

    public function test_manifest_public_api_is_stable(): void
    {
        foreach (['fromArray', 'loadFromFile', 'isCompatibleWith', 'toArray'] as $method) {
            $this->assertTrue(
                method_exists('Magna\Plugins\Manifest', $method),
                "Manifest::{$method}() must not be removed or renamed.",
            );
        }

        foreach (['resolveBootOrder', 'assertCanEnable'] as $method) {
            $this->assertTrue(
                method_exists('Magna\Plugins\DependencyResolver', $method),
                "DependencyResolver::{$method}() must not be removed or renamed.",
            );
        }
    }
}
