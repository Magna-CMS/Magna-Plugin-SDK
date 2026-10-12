<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * One plugin-owned section of a site-kit bundle: how that plugin's entities
 * travel when a site is exported, diffed, and promoted between environments.
 *
 * A site kit exists so standing a site up is one apply, not an apply plus a
 * README of manual steps. Any plugin whose entities a page can reference —
 * a form a form block selects, a product a listing block mounts — implements
 * this, or every kit that uses those blocks ships with an invisible hole an
 * admin must hand-fill.
 *
 * Rows must reference by stable, human-authored identity (handle, slug,
 * name), never by id: ids are environment facts, and a kit's whole job is to
 * cross environments. Apply is an UPSERT and must tolerate a malformed row by
 * skipping it — a kit is a file somebody can edit — while throwing only on
 * genuine failure, which aborts the kit's one transaction.
 */
interface SiteKitSection
{
    /**
     * The section's top-level key in the kit document. Must contain a colon
     * ('plugin-short-name:entity', e.g. 'forms:forms'): the namespace is what
     * keeps plugin sections from ever colliding with the kit's own keys, and
     * the reader rejects a section without one.
     */
    public function key(): string;

    /**
     * Every row of this section as it stands locally, deterministically
     * ordered so two exports of the same site are byte-identical.
     *
     * @return list<array<string, mixed>>
     */
    public function export(): array;

    /**
     * What applying these rows would change, without changing anything,
     * keyed the same way the rows are (handle/slug/name).
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{added: list<string>, changed: list<string>, localOnly: list<string>}
     */
    public function diff(array $rows): array;

    /**
     * Upsert the rows; returns how many were written. Runs inside the kit's
     * transaction — skip a malformed row, throw only when the section as a
     * whole cannot apply.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function apply(array $rows, ?string $actorId = null): int;
}
