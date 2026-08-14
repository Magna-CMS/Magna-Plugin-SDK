<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Magna\Blocks\Conditions\DisplayCondition;

/**
 * Plugin contract: contributes display conditions — named show/hide rules a
 * page node carries in `settings.conditions` and the renderer evaluates on
 * every uncached render.
 *
 * Pages ships two types, `auth` and `schedule`. This is how the rest arrive:
 * a commerce plugin gating a banner on a basket, a membership plugin gating a
 * section on a plan, a support plugin gating a notice on an open ticket.
 *
 * Each condition declares its cacheability alongside its answer; the page
 * cache folds those declarations into its verdict, so a rule that reads
 * something about the individual visitor keeps every page carrying it out of
 * the shared cache. An unregistered type hides the node and makes the page
 * uncacheable — which is also what a condition left behind by a disabled
 * plugin does, and the safe direction for it to fail in.
 *
 * Boundary note: like RegistersBlocks and RegistersDynamicTags, this contract
 * references a core type (Magna\Blocks\Conditions\DisplayCondition) — a
 * behavioural primitive with an evaluate contract, not worth duplicating into
 * the SDK. A condition-providing plugin compiles against magna/core, which it
 * requires at runtime anyway.
 */
interface RegistersDisplayConditions
{
    /**
     * Return the display conditions provided by this plugin.
     *
     * @return list<DisplayCondition>
     */
    public function displayConditions(): array;
}
