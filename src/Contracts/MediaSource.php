<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * A body of files a plugin stores itself, listed in the core media library.
 *
 * Plugins that keep uploads in the shared library (via the core ingestor) need
 * none of this — those files are already there. This exists for the ones that
 * cannot: compliance documents on a private disk, message attachments, per-
 * tenant archives. Before it, such files were invisible to the operator, who
 * had a screen promising every file in one place and no way to learn what the
 * promise was leaving out.
 *
 * A source is read-only by design. The library lists and links; it never
 * streams, deletes or moves a file it does not own, because the plugin holding
 * it is the only thing that knows who is allowed to see it.
 */
interface MediaSource
{
    /**
     * Stable machine key, unique across plugins, e.g. "embhas.documents".
     *
     * Prefix it with the plugin name: it lands in URLs and in the operator's
     * saved filters, so a rename is a broken link.
     */
    public function key(): string;

    /** Shown as the tab label, e.g. "Empanelment documents". */
    public function label(): string;

    /**
     * Permission an operator must hold before this source is even listed.
     *
     * Null exposes it to anyone who can open the media library. Say so
     * deliberately — for confidential sources it is the wrong answer, since
     * even the file names ("aadhaar-front.jpg", "termination-letter.pdf") tell
     * a story their owner did not consent to sharing.
     */
    public function permission(): ?string;

    /**
     * True when the library must show metadata only: no thumbnail, no preview,
     * no download, no direct storage URL.
     *
     * The plugin decides this, not the operator and not core. If any file in
     * the source could be personal or contractual, the answer is true.
     */
    public function isConfidential(): bool;

    /** Total files, for the tab's count badge. */
    public function count(): int;

    /**
     * One page of files, newest first.
     *
     * @param  int  $page  1-based.
     * @param  string  $search  Empty when the operator has not searched.
     * @return list<MediaSourceItem>
     */
    public function items(int $page, int $perPage, string $search = ''): array;
}
