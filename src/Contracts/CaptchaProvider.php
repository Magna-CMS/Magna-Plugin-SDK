<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * A captcha/bot-protection provider (Cloudflare Turnstile, hCaptcha, …).
 * Semver-guaranteed from core 1.0.
 *
 * Core binds a null implementation by default so a site with no captcha plugin
 * behaves as if every attempt passes. A plugin such as Magna Defence rebinds
 * this contract to a real provider. Consumers (login forms, the Captcha
 * validation rule) depend only on this interface, never on the provider.
 *
 * Fail behaviour is the provider's responsibility, not the caller's: verify()
 * returns a passing CaptchaResult when the provider is disabled/unconfigured,
 * and fails closed only on a definitive negative verdict from a reachable
 * backend. The expected action and allowed hostnames for a surface are
 * server-side configuration the provider owns — they are deliberately NOT part
 * of CaptchaAttempt, so a request can never choose which action/hostname is
 * trusted.
 */
interface CaptchaProvider
{
    /** True when a real provider is configured and switched on. */
    public function enabled(): bool;

    /** Short machine identifier for the active provider, e.g. 'turnstile'. */
    public function provider(): string;

    /**
     * The public site key to render the widget for a surface, or null when the
     * provider is disabled. Site keys are public by design.
     */
    public function siteKey(string $surface): ?string;

    /** Verify a single attempt. Never throws for operational failures. */
    public function verify(CaptchaAttempt $attempt): CaptchaResult;
}
