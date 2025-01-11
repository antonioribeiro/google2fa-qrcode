<?php

namespace PragmaRX\Google2FAQRCode\Tests;

use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQRCodeServiceException;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use Zxing\QrReader;

class Google2FATest extends TestCase
{
    const EMAIL = 'acr+pragmarx@antoniocarlosribeiro.com';

    const OTP_URL = 'otpauth://totp/PragmaRX:acr+pragmarx@antoniocarlosribeiro.com?secret=ADUMJO5634NPDEKW&issuer=PragmaRX&algorithm=SHA1&digits=6&period=30';

    public function setUp(): void
    {
        $this->google2fa = new Google2FA();
    }

    public function readQRCode($data)
    {
        [, $data] = explode(';', $data);

        [, $data] = explode(',', $data);

        return rawurldecode(
            (new QrReader(
                base64_decode($data),
                QrReader::SOURCE_TYPE_BLOB
            ))->text()
        );
    }

    public function testQrcodeServiceMissing()
    {
        $this->expectException(MissingQRCodeServiceException::class);

        $this->google2fa->setQRCodeService(null);

        $this->getQRCode();
    }

    public function testQrcodeInlineBacon()
    {
        if (!(new Bacon())->imagickIsAvailable()) {
            $this->assertTrue(true);

            return;
        }

        $this->google2fa->setQRCodeService(new Bacon());

        $this->assertEquals(
            static::OTP_URL,
            $this->readQRCode($this->getQRCode())
        );

        $google2fa = new Google2FA(null, new Bacon(new \BaconQrCode\Renderer\Image\SvgImageBackEnd()));

        $this->assertEquals(
            static::OTP_URL,
            $this->readQRCode($this->getQRCode())
        );
    }

    public function testQrcodeInlineChillerlan()
    {
        $this->google2fa->setQRCodeService(new Chillerlan());

        $this->assertStringStartsWith(
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMj',
            $this->getQRCode()
        );
    }

    public function getQRCode()
    {
        return $this->google2fa->getQRCodeInline(
            'PragmaRX',
            static::EMAIL,
            Constants::SECRET
        );
    }
}
