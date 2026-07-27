<?php

declare(strict_types=1);

// Minimal stubs for the Magna Core types the SDK contracts reference by design
// (see README "Package boundary"). Present only for standalone static analysis;
// at runtime the real classes are provided by magna/core, which every plugin
// installs. Keep this in sync with the real signatures the SDK contracts use.

namespace Magna\Blocks;

/**
 * @see \Magna\Contracts\RegistersBlocks
 */
class BlockDefinition {}
