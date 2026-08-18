<?php

declare(strict_types=1);

namespace Magna\Contracts;

/**
 * The outcome of a captcha verification.
 * Semver-guaranteed from core 1.0.
 *
 * Never carries the secret or the raw token. Error codes are for server-side
 * logging only and must not be shown verbatim to the end user (they are an
 * oracle for tuning bypasses). Hostname and action are the verified values the
 * backend reported, for the provider's own strict comparison and for auditing.
 */
final class CaptchaResult
{
    /**
     * @param  list<string>  $errorCodes
     */
    public function __construct(
        public readonly bool $passed,
        public readonly array $errorCodes = [],
        public readonly ?string $hostname = null,
        public readonly ?string $action = null,
        public readonly ?string $challengeTs = null,
    ) {}

    public static function pass(
        ?string $hostname = null,
        ?string $action = null,
        ?string $challengeTs = null,
    ): self {
        return new self(true, [], $hostname, $action, $challengeTs);
    }

    /**
     * @param  list<string>  $errorCodes
     */
    public static function fail(
        array $errorCodes = [],
        ?string $hostname = null,
        ?string $action = null,
    ): self {
        return new self(false, $errorCodes, $hostname, $action);
    }
}
