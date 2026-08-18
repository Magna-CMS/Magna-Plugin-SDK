<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: declares captcha-protectable surfaces this plugin exposes.
 * Semver-guaranteed from core 1.0.
 *
 * A security plugin collects these to render per-surface toggles, grouped by
 * plugin, with no hardcoded knowledge of which plugins exist. A plugin that
 * declares no surface is simply never offered captcha protection.
 */
interface RegistersCaptchaSurfaces
{
    /**
     * @return list<CaptchaSurface>
     */
    public function captchaSurfaces(): array;
}
