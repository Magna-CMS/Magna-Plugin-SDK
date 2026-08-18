<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * How the login-check seam treats an *unexpected* throwable from a LoginCheck.
 * Semver-guaranteed from core 1.0.
 *
 * A check denies by throwing a ValidationException and continues by returning;
 * it never returns a boolean and never mutates auth state, so it cannot turn a
 * denial into an approval. This enum governs only the remaining case — a check
 * that crashes with some *other* exception (a bug):
 *
 *  - Open   — availability-first. The crash is logged and login continues.
 *             Correct for captcha, where the remaining gates (throttle, 2FA,
 *             password) still stand and a buggy check must not lock admins out.
 *  - Closed — the crash denies the attempt. Correct for a check whose whole
 *             purpose is exclusion (e.g. an IP allowlist), where failing open
 *             would be a bypass.
 */
enum FailMode
{
    case Open;
    case Closed;
}
