<?php

namespace PragmaRX\Google2FAQRCode\Tests;

use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\Png;
use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Zxing\QrReader;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQRCodeServiceException;

class Google2FATest extends TestCase
{
    const EMAIL = 'acr+pragmarx@antoniocarlosribeiro.com';

    const OTP_URL = 'otpauth://totp/PragmaRX:acr+pragmarx@antoniocarlosribeiro.com?secret=ADUMJO5634NPDEKW&issuer=PragmaRX&algorithm=SHA1&digits=6&period=30';

    protected $google2fa;

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

    public function testQRCodeServiceMissing()
    {
        $this->expectException(MissingQRCodeServiceException::class);

        $this->google2fa->setQRCodeService(null);

        $this->getQRCode();
    }

    public function testQRCodeInlineBacon()
    {
        if (!(new Bacon())->imagickIsAvailable()) {
            $this->markTestSkipped('imagick extension not available');
        }

        $this->google2fa->setQRCodeService(new Bacon());

        $this->assertEquals(
            static::OTP_URL,
            $this->readQRCode($this->getQRCode())
        );
    }

    public function testQRCodeInlineBaconSvg()
    {
        $this->google2fa->setQRCodeService(
            new Bacon(new \BaconQrCode\Renderer\Image\SvgImageBackEnd())
        );

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $this->getQRCode());
    }

    public function testQRCodeInlineChillerlan()
    {
        $this->google2fa->setQRCodeService(new Chillerlan());

        $output = $this->getQRCode();

        $prefix = 'data:image/svg+xml;base64,';
        $this->assertStringStartsWith($prefix, $output);

        // Decode and confirm the payload is real SVG markup. v5+ may
        // prepend an XML declaration; v2-v4 emit the <svg> tag directly.
        // Either way the decoded content must contain <svg.
        $decoded = base64_decode(substr($output, strlen($prefix)));
        $this->assertStringContainsString('<svg', $decoded);
    }

    public function testFactoryPrefersBacon()
    {
        $this->assertInstanceOf(Bacon::class, $this->google2fa->qrCodeServiceFactory());
    }

    public function testSetQrCodeServiceIsFluent()
    {
        $result = $this->google2fa->setQRCodeService(new Chillerlan());

        $this->assertSame($this->google2fa, $result);
    }

    public function testGetQrCodeServiceReturnsAssignedService()
    {
        $service = new Chillerlan();
        $this->google2fa->setQRCodeService($service);

        $this->assertSame($service, $this->google2fa->getQRCodeService());
    }

    public function testConstructorAcceptsExplicitService()
    {
        $service = new Chillerlan();
        $google2fa = new Google2FA($service);

        $this->assertSame($service, $google2fa->getQRCodeService());
    }

    public function testChillerlanBuildOptionsArrayReturnsDefaults()
    {
        $options = (new Chillerlan())->buildOptionsArray();

        $this->assertArrayHasKey('eccLevel', $options);
        $this->assertArrayHasKey('version', $options);
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
