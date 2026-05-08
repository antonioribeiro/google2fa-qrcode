<?php

namespace PragmaRX\Google2FAQRCode\Tests;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRMarkupSVG;
use PHPUnit\Framework\Attributes\Group;
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
        $this->expectExceptionMessage('install a service package');

        $this->google2fa->setQRCodeService(null);

        $this->getQRCode();
    }

    /**
     * @group bacon
     */
    #[Group('bacon')]
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

    /**
     * @group bacon
     */
    #[Group('bacon')]
    public function testQRCodeInlineBaconSvg()
    {
        $this->google2fa->setQRCodeService(
            new Bacon(new \BaconQrCode\Renderer\Image\SvgImageBackEnd())
        );

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $this->getQRCode());
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testQRCodeInlineChillerlan()
    {
        $this->google2fa->setQRCodeService(new Chillerlan());

        $output = $this->getQRCode();

        $prefix = 'data:image/svg+xml;base64,';
        $this->assertStringStartsWith($prefix, $output);

        // Decode and confirm the payload is real SVG markup.
        $decoded = base64_decode(substr($output, strlen($prefix)));
        $this->assertStringContainsString('<svg', $decoded);
    }

    /**
     * @group bacon
     */
    #[Group('bacon')]
    public function testFactoryPrefersBacon()
    {
        $this->assertInstanceOf(Bacon::class, $this->google2fa->qrCodeServiceFactory());
    }

    /**
     * Asserts the factory falls back to Chillerlan when Bacon is absent.
     * Only meaningful in CI rows where Bacon has been removed; the default
     * phpunit run excludes this group so the skip doesn't trip --fail-on-skipped.
     *
     * @group bacon-absent
     */
    #[Group('bacon-absent')]
    public function testFactoryFallsBackToChillerlanWhenBaconAbsent()
    {
        if (class_exists(\BaconQrCode\Writer::class)) {
            $this->markTestSkipped('Bacon is installed; factory prefers it.');
        }

        $this->assertInstanceOf(Chillerlan::class, $this->google2fa->qrCodeServiceFactory());
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testSetQrCodeServiceIsFluent()
    {
        $result = $this->google2fa->setQRCodeService(new Chillerlan());

        $this->assertSame($this->google2fa, $result);
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testGetQrCodeServiceReturnsAssignedService()
    {
        $service = new Chillerlan();
        $this->google2fa->setQRCodeService($service);

        $this->assertSame($service, $this->google2fa->getQRCodeService());
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testConstructorAcceptsExplicitService()
    {
        $service = new Chillerlan();
        $google2fa = new Google2FA($service);

        $this->assertSame($service, $google2fa->getQRCodeService());
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testChillerlanBuildOptionsArrayReturnsDefaults()
    {
        $options = (new Chillerlan())->buildOptionsArray();

        $this->assertSame(EccLevel::L, $options['eccLevel']);
        $this->assertSame(Version::AUTO, $options['version']);
        $this->assertSame(QRMarkupSVG::class, $options['outputInterface']);
        $this->assertFalse($options['outputBase64']);
    }

    /**
     * @group chillerlan
     */
    #[Group('chillerlan')]
    public function testChillerlanSetOptionsCannotOverrideOutputBase64()
    {
        $service = new Chillerlan();
        $service->setOptions(['outputBase64' => true]);

        // The package owns the base64 wrap, so user-supplied
        // outputBase64 must be forced to false in the resolved options.
        $this->assertFalse($service->buildOptionsArray()['outputBase64']);
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
