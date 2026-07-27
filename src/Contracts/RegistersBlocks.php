<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Magna\Blocks\BlockDefinition;

/**
 * Plugin contract: contributes block definitions to the BlockRegistry.
 * Semver-guaranteed from core 1.0.
 *
 * The plugin's blocks() method returns a list of BlockDefinition value objects,
 * which are registered into the core BlockRegistry when the plugin is enabled.
 *
 * Boundary note: this is the one SDK contract that intentionally references a
 * core type (Magna\Blocks\BlockDefinition). Block definitions are a rich,
 * core-owned schema primitive with their own field/option graph, so they are
 * not duplicated into the SDK. A block-providing plugin therefore compiles
 * against magna/core (which it requires at runtime regardless). Every other
 * SDK contract is expressed purely in SDK, Laravel-contract, or scalar types.
 */
interface RegistersBlocks
{
    /**
     * Return the block definitions provided by this plugin.
     *
     * @return list<BlockDefinition>
     */
    public function blocks(): array;
}
