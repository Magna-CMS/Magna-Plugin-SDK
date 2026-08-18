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

namespace Magna\Blocks\DataSources;

/**
 * @see \Magna\Contracts\RegistersDataSources
 */
interface DataSource {}

namespace Magna\Blocks\Conditions;

/**
 * @see \Magna\Contracts\RegistersDisplayConditions
 */
interface DisplayCondition {}

namespace Magna\Blocks\DynamicTags;

/**
 * @see \Magna\Contracts\RegistersDynamicTags
 */
interface DynamicTag {}

namespace Magna\Frontend;

/**
 * @see \Magna\Contracts\ProvidesFrontendPages
 */
class FrontendPage {}

namespace Illuminate\Http;

/**
 * Laravel's HTTP request, referenced by \Magna\Contracts\LoginCheck::assert().
 * Provided at runtime by the host application; stubbed here for standalone
 * static analysis of the otherwise dependency-free SDK.
 */
class Request {}
