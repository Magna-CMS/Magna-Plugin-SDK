<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Magna\Blocks\DynamicTags\DynamicTag;

/**
 * Plugin contract: contributes dynamic tags — named live values (an unread
 * count, a stock level, the current year) that page fields bind to and the
 * renderer resolves on every uncached render.
 *
 * Each tag declares its cacheability (static / page / user); the page cache
 * folds those declarations into its verdict, so a per-user tag keeps every
 * page that binds it out of the shared cache.
 *
 * Boundary note: like RegistersBlocks, this contract references a core
 * type (Magna\Blocks\DynamicTags\DynamicTag) — a behavioral primitive with
 * a resolve contract, not worth duplicating into the SDK. A tag-providing
 * plugin compiles against magna/core, which it requires at runtime anyway.
 */
interface RegistersDynamicTags
{
    /**
     * Return the dynamic tags provided by this plugin.
     *
     * @return list<DynamicTag>
     */
    public function dynamicTags(): array;
}
