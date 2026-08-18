<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * Which keypair a captcha surface verifies against.
 * Semver-guaranteed from core 1.0.
 *
 * A backed enum so a typo becomes a compile error rather than a silently
 * unprotected surface. Admin surfaces use a tightly host-locked keypair;
 * portal/public surfaces use one whose hostname allowlist grows with customer
 * domains. Keeping them separate limits blast radius: a domain added for a
 * public form can never mint a token the admin login accepts.
 */
enum CaptchaKeypair: string
{
    case Admin = 'admin';
    case Portal = 'portal';
}
