<?php

namespace PragmaRX\Google2FAQRCode\Tests;

use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FAQRCode\Google2FA;

class Google2FATest extends TestCase
{
    public function setUp()
    {
        $this->google2fa = new Google2FA();
    }

    public function testQrcodeInline()
    {
        $this->assertEquals(
            $this->getQRCodeStringConstant(),
            $this->google2fa->getQRCodeInline('PragmaRX', 'acr+pragmarx@antoniocarlosribeiro.com', Constants::SECRET)
        );
    }

    public function getQRCodeStringConstant()
    {
        return $this->google2fa->getBaconQRCodeVersion() === 1
            ? phpversion() >= '7.2' ? Constants::QRCODEPHPABOVE72_V1 : Constants::QRCODEPHPBELOW72_V1
            : Constants::QRCODEPHPABOVE72_V2;
    }
}
