<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Magna\Frontend\FrontendPage;

/**
 * Plugin contract: contributes public pages to the site. The Pages plugin
 * mounts each declared route under the site router, renders its view
 * inside the active layout shell (site header/footer, theme tokens), and
 * offers it in the menu builder — a plugin screen that feels native to
 * the site without the plugin knowing anything about themes or menus.
 *
 * Auth requirements declared on a page are enforced before render, and
 * menu items pointing at a page the current visitor may not open are
 * hidden automatically.
 *
 * Boundary note: like RegistersBlocks, this contract references a core
 * type (Magna\Frontend\FrontendPage) — a definition VO, not worth
 * duplicating into the SDK. A page-providing plugin compiles against
 * magna/core, which it requires at runtime anyway.
 */
interface ProvidesFrontendPages
{
    /**
     * Return the frontend pages provided by this plugin.
     *
     * @return list<FrontendPage>
     */
    public function frontendPages(): array;
}
