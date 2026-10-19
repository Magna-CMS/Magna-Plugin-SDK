<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: adds columns and filters to a content type's admin entry
 * list. Semver-guaranteed from core 1.0.
 *
 * The return types are plain arrays rather than typed Filament component lists
 * on purpose, exactly like ExtendsEntryForm: Filament is not an SDK dependency
 * (the SDK stays dependency-free beyond illuminate/contracts +
 * composer/semver). Core merges the returned components into the entry table
 * when it builds the panel; on a core that does not read this contract yet,
 * implementing it is inert and costs nothing.
 *
 * This file was once lost — it existed only in an unversioned SDK source copy,
 * and a fresh checkout dropped it, which made every plugin class implementing
 * it unloadable and rolled back their installs. It is in version control now;
 * removing it again is a breaking change for every such plugin.
 */
interface ExtendsEntryTable
{
    /**
     * Return Filament table columns to append to the entry list for the given content type.
     *
     * @return array<int, mixed>
     */
    public function entryTableColumns(string $contentType): array;

    /**
     * Return Filament table filters to append to the entry list for the given content type.
     *
     * @return array<int, mixed>
     */
    public function entryTableFilters(string $contentType): array;
}
