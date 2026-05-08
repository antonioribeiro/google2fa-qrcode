<?php

namespace PragmaRX\Google2FAQRCode;

use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer;
use chillerlan\QRCode\QRCode;
use PragmaRX\Google2FA\Google2FA as Google2FAPackage;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQRCodeServiceException;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract;

class Google2FA extends Google2FAPackage
{
    /**
     * @var QRCodeServiceContract|null
     */
    protected $qrCodeService;

    public function __construct(
        ?QRCodeServiceContract $qrCodeService = null,
        ?ImageBackEndInterface $imageBackEnd = null
    ) {
        $this->setQRCodeService(
            $qrCodeService === null
                ? $this->qrCodeServiceFactory($imageBackEnd)
                : $qrCodeService
        );
    }

    /**
     * Generates a QR code data url to display inline.
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInline(
        $company,
        $holder,
        $secret,
        $size = 200,
        $encoding = 'utf-8'
    ) {
        $service = $this->getQRCodeService();

        if ($service === null) {
            throw new MissingQRCodeServiceException(
                'You need to install a service package or assign yourself the service to be used.'
            );
        }

        return $service->getQRCodeInline(
            $this->getQRCodeUrl($company, $holder, $secret),
            $size,
            $encoding
        );
    }

    public function getQRCodeService(): ?QRCodeServiceContract
    {
        return $this->qrCodeService;
    }

    public function setQRCodeService(?QRCodeServiceContract $service): self
    {
        $this->qrCodeService = $service;

        return $this;
    }

    public function qrCodeServiceFactory(?ImageBackEndInterface $imageBackEnd = null): ?QRCodeServiceContract
    {
        if (
            class_exists(Writer::class) &&
            class_exists(ImageRenderer::class)
        ) {
            return new Bacon($imageBackEnd);
        }

        if (class_exists(QRCode::class)) {
            return new Chillerlan();
        }

        return null;
    }
}
