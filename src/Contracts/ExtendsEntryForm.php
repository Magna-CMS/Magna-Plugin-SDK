<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: adds fields or tabs to a content type's admin entry form.
 * Semver-guaranteed from core 1.0.
 *
 * The return type is a plain array rather than a typed Filament component list
 * on purpose: Filament is not an SDK dependency (the SDK stays dependency-free
 * beyond illuminate/contracts + composer/semver). Core merges the returned
 * Filament components into the entry form when it builds the panel.
 */
interface ExtendsEntryForm
{
    /**
     * Return Filament form components to append to the entry form for the given content type.
     *
     * @return array<int, mixed>
     */
    public function entryFormExtensions(string $contentType): array;
}
