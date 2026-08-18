<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * One captcha verification attempt: the minimal, caller-producible facts.
 * Semver-guaranteed from core 1.0.
 *
 * Deliberately carries only what a generic caller controls — the client token,
 * the server-authored surface identifier, and the client IP. The expected
 * action and allowed hostnames are server-side configuration the provider
 * resolves from the surface; keeping them off this object is what stops a
 * request from nominating which action/hostname the server should trust.
 */
final class CaptchaAttempt
{
    public function __construct(
        public readonly ?string $token,
        public readonly string $surface,
        public readonly ?string $ip = null,
    ) {}
}
