<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: contributes Artisan console commands.
 * Semver-guaranteed from core 1.0.
 *
 * Core registers these through the service provider while the console kernel is
 * being built, so a plugin's commands are available on the CLI without the
 * plugin needing to reach into Laravel's console internals itself.
 */
interface RegistersCommands
{
    /**
     * Fully-qualified Artisan command class names this plugin provides. Each
     * must extend Illuminate\Console\Command (provided by core at runtime).
     *
     * @return list<class-string>
     */
    public function commands(): array;
}
