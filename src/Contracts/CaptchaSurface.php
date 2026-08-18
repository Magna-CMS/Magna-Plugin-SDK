<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * A captcha-protectable form a plugin exposes, so a security plugin can offer a
 * toggle for it without knowing the plugin exists.
 * Semver-guaranteed from core 1.0.
 *
 * The key is a stable, server-authored identifier (e.g. 'roya.erp.login') that
 * the Captcha validation rule and the provider both key on. The toggle a
 * dashboard renders from this is advisory UI only — actual enforcement is the
 * server-side surface-enabled check inside the provider's verify path.
 */
final class CaptchaSurface
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $group,
        public readonly CaptchaKeypair $keypair,
        public readonly bool $defaultEnabled = true,
    ) {}
}
