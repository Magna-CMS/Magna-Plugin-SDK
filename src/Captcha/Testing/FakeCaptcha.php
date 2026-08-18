<?php

declare(strict_types=1);

namespace Magna\Captcha\Testing;

use Magna\Contracts\CaptchaAttempt;
use Magna\Contracts\CaptchaProvider;
use Magna\Contracts\CaptchaResult;

/**
 * A test double for CaptchaProvider, so a consumer's suite can exercise a
 * captcha-protected surface without a real backend.
 *
 * Bind an instance to the CaptchaProvider contract in a test, drive it with
 * shouldPass()/shouldFail()/disabled(), then read the recorded attempts. It
 * deliberately makes no test-framework assertions itself — it exposes state and
 * lets the caller assert with whatever framework they use — so it can ship in
 * the runtime autoloader without pulling PHPUnit into consumers.
 */
final class FakeCaptcha implements CaptchaProvider
{
    private bool $enabled = true;

    private bool $passes = true;

    /** @var list<string> */
    private array $failCodes = [];

    /** @var list<CaptchaAttempt> */
    private array $attempts = [];

    public function shouldPass(): self
    {
        $this->passes = true;
        $this->failCodes = [];

        return $this;
    }

    public function shouldFail(string ...$codes): self
    {
        $this->passes = false;
        $this->failCodes = $codes === [] ? ['fake-failure'] : array_values($codes);

        return $this;
    }

    public function disabled(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function provider(): string
    {
        return 'fake';
    }

    public function siteKey(string $surface): ?string
    {
        return $this->enabled ? 'fake-site-key' : null;
    }

    public function verify(CaptchaAttempt $attempt): CaptchaResult
    {
        $this->attempts[] = $attempt;

        if (! $this->enabled) {
            return CaptchaResult::pass();
        }

        return $this->passes
            ? CaptchaResult::pass('fake.test', $attempt->surface)
            : CaptchaResult::fail($this->failCodes);
    }

    /**
     * Every attempt passed to verify(), in order.
     *
     * @return list<CaptchaAttempt>
     */
    public function attempts(): array
    {
        return $this->attempts;
    }

    public function wasVerified(string $surface): bool
    {
        foreach ($this->attempts as $attempt) {
            if ($attempt->surface === $surface) {
                return true;
            }
        }

        return false;
    }

    public function verifiedNothing(): bool
    {
        return $this->attempts === [];
    }
}
