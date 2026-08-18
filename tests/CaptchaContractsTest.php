<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Magna\Captcha\NullCaptcha;
use Magna\Captcha\Testing\FakeCaptcha;
use Magna\Contracts\CaptchaAttempt;
use Magna\Contracts\CaptchaKeypair;
use Magna\Contracts\CaptchaResult;
use Magna\Contracts\CaptchaSurface;
use PHPUnit\Framework\TestCase;

final class CaptchaContractsTest extends TestCase
{
    public function test_null_captcha_is_disabled_and_always_passes(): void
    {
        $null = new NullCaptcha;

        $this->assertFalse($null->enabled());
        $this->assertSame('null', $null->provider());
        $this->assertNull($null->siteKey('magna.admin.login'));

        $result = $null->verify(new CaptchaAttempt('anything', 'magna.admin.login', '203.0.113.1'));

        $this->assertTrue($result->passed);
        $this->assertSame([], $result->errorCodes);
    }

    public function test_result_factories_set_passed_flag(): void
    {
        $pass = CaptchaResult::pass('example.com', 'magna.admin.login', '2026-08-16T00:00:00Z');
        $this->assertTrue($pass->passed);
        $this->assertSame('example.com', $pass->hostname);
        $this->assertSame('magna.admin.login', $pass->action);

        $fail = CaptchaResult::fail(['invalid-input-response']);
        $this->assertFalse($fail->passed);
        $this->assertSame(['invalid-input-response'], $fail->errorCodes);
    }

    public function test_keypair_enum_is_backed_by_stable_strings(): void
    {
        $this->assertSame('admin', CaptchaKeypair::Admin->value);
        $this->assertSame('portal', CaptchaKeypair::Portal->value);
    }

    public function test_surface_value_object_is_readonly(): void
    {
        $surface = new CaptchaSurface(
            key: 'roya.erp.login',
            label: 'Roya ERP — portal sign-in',
            group: 'Roya ERP',
            keypair: CaptchaKeypair::Portal,
        );

        $this->assertSame('roya.erp.login', $surface->key);
        $this->assertTrue($surface->defaultEnabled);
        $this->assertSame(CaptchaKeypair::Portal, $surface->keypair);
    }

    public function test_fake_records_attempts_and_can_be_driven_to_fail(): void
    {
        $fake = new FakeCaptcha;

        $this->assertTrue($fake->verifiedNothing());

        $pass = $fake->verify(new CaptchaAttempt('token', 'roya.erp.login', '203.0.113.9'));
        $this->assertTrue($pass->passed);
        $this->assertTrue($fake->wasVerified('roya.erp.login'));
        $this->assertFalse($fake->wasVerified('magna.admin.login'));

        $fake->shouldFail('timeout-or-duplicate');
        $fail = $fake->verify(new CaptchaAttempt('token', 'roya.erp.login'));
        $this->assertFalse($fail->passed);
        $this->assertSame(['timeout-or-duplicate'], $fail->errorCodes);

        $this->assertCount(2, $fake->attempts());
    }

    public function test_fake_disabled_passes_regardless(): void
    {
        $fake = (new FakeCaptcha)->shouldFail()->disabled();

        $this->assertFalse($fake->enabled());
        $this->assertTrue($fake->verify(new CaptchaAttempt(null, 'magna.admin.login'))->passed);
    }
}
