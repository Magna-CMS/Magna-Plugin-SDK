<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Plugin contract: contributes pre-authentication checks to the login seam.
 * Semver-guaranteed from core 1.0.
 *
 * Core collects the checks from every enabled plugin and runs them before
 * credential verification. A check may be returned as an instance or a
 * class-string to be resolved from the container.
 */
interface RegistersLoginChecks
{
    /**
     * @return list<class-string<LoginCheck>|LoginCheck>
     */
    public function loginChecks(): array;
}
