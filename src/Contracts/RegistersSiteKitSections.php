<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: declares the sections this plugin contributes to a
 * site-kit bundle (export / diff / promote between environments).
 *
 * Implement this whenever the plugin owns entities a page can reference by
 * handle — otherwise a kit that uses them applies cleanly and renders a hole
 * until an admin recreates each one by hand, which is exactly the manual step
 * a kit exists to remove. A kit carrying a section no local plugin registers
 * is skipped and reported, never fatal: an older installation reads a newer
 * kit and simply says what it could not place.
 */
interface RegistersSiteKitSections
{
    /**
     * @return list<SiteKitSection>
     */
    public function siteKitSections(): array;
}
