<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Magna\Blocks\DataSources\DataSource;

/**
 * Plugin contract: contributes data sources the Pages Loop block can
 * iterate — the seam that puts a plugin's data (job openings, products,
 * upcoming events) onto visually built pages without the plugin knowing
 * anything about page building.
 *
 * Boundary note: like RegistersBlocks, this contract references a core
 * type (Magna\Blocks\DataSources\DataSource) — a behavioral primitive with
 * a fetch contract, not worth duplicating into the SDK. A source-providing
 * plugin compiles against magna/core, which it requires at runtime anyway.
 */
interface RegistersDataSources
{
    /**
     * Return the data sources provided by this plugin.
     *
     * @return list<DataSource>
     */
    public function dataSources(): array;
}
