<?php

declare(strict_types=1);

namespace Magna\Contracts;

use DateTimeInterface;

/**
 * One file held by a plugin, described for the core media library.
 *
 * This is a description, not the file. The library lists what a plugin holds
 * without gaining any ability to read it: a confidential source hands over the
 * name, size and owner and nothing else, and $manageUrl sends the operator to
 * the plugin's own screen, where that plugin's own authorisation runs.
 *
 * That split is the whole point. "Every file in one place" must not turn into
 * "every file downloadable from one place" — a media library that renders a
 * scan of somebody's passport because a plugin happened to store one is a
 * privacy breach, not a feature.
 */
final readonly class MediaSourceItem
{
    /**
     * @param  string  $id  Identifier within the owning source, not globally unique.
     * @param  string  $name  Shown to the operator. Never a storage path.
     * @param  int  $sizeBytes  0 when the size is not known.
     * @param  string|null  $ownerLabel  Who or what the file belongs to, e.g. "Anita Rao — application".
     * @param  string|null  $manageUrl  Where the operator handles this file. Null when the plugin has no such screen.
     * @param  string|null  $thumbnailUrl  Only ever set by a non-confidential source.
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $mimeType,
        public int $sizeBytes = 0,
        public ?DateTimeInterface $uploadedAt = null,
        public ?string $ownerLabel = null,
        public ?string $manageUrl = null,
        public ?string $thumbnailUrl = null,
    ) {}
}
