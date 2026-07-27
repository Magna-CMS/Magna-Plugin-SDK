<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: enriches delivery API entry payloads with extra keys.
 *
 * Implement this contract to inject plugin-owned data (e.g. SEO meta, computed
 * fields) into every delivery API response for entries of a given content type.
 * The decorator runs after the core transformer and may modify $payload freely.
 *
 * Semver-guaranteed from core 1.0.
 */
interface DecoratesDeliveryResponse
{
    /**
     * Add or modify keys in an entry's delivery API payload.
     * Modify $payload in-place — the array is passed by reference.
     *
     * @param  array<string, mixed>  $payload
     */
    public function decorateDeliveryEntry(string $contentType, string $entryId, array &$payload): void;
}
