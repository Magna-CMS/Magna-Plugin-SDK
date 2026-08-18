<?php

declare(strict_types=1);

namespace Magna\Captcha;

use Magna\Contracts\CaptchaAttempt;
use Magna\Contracts\CaptchaProvider;
use Magna\Contracts\CaptchaResult;

/**
 * The default CaptchaProvider: no provider configured, every attempt passes.
 *
 * Core binds this so a site with no captcha plugin behaves normally. A real
 * provider (Magna Defence) rebinds the contract; until then this keeps the
 * Captcha rule and login checks inert rather than blocking.
 */
final class NullCaptcha implements CaptchaProvider
{
    public function enabled(): bool
    {
        return false;
    }

    public function provider(): string
    {
        return 'null';
    }

    public function siteKey(string $surface): ?string
    {
        return null;
    }

    public function verify(CaptchaAttempt $attempt): CaptchaResult
    {
        return CaptchaResult::pass();
    }
}
