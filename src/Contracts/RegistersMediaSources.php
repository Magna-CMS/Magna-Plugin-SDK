<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: declares files the plugin stores outside the media library.
 *
 * Implement this whenever the plugin writes an upload anywhere other than
 * through the core media ingestor. The media library is sold to operators as
 * the one place that accounts for every file on the installation; a plugin
 * that stores files and stays quiet about it makes that claim false, and the
 * operator has no way of finding out short of reading the disk.
 *
 * Declaring a source does not expose the files. A confidential source lists
 * names and sizes and links back to the plugin's own screen — see MediaSource.
 */
interface RegistersMediaSources
{
    /**
     * @return iterable<MediaSource>
     */
    public function mediaSources(): iterable;
}
