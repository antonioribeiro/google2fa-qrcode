<?php

namespace PragmaRX\Google2FAQRCode;

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

    /**
     * Google2FA constructor.
     *
     * @param QRCodeServiceContract|null $qrCodeService
     * @param mixed $imageBackEnd
     */
    public function __construct(?QRCodeServiceContract $qrCodeService = null, $imageBackEnd = null)
    {
        $this->setQRCodeService(
            empty($qrCodeService)
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
        if (empty($this->getQRCodeService())) {
            throw new MissingQRCodeServiceException(
                'You need to install a service package or assign yourself the service to be used.'
            );
        }

        return $this->qrCodeService->getQRCodeInline(
            $this->getQRCodeUrl($company, $holder, $secret),
            $size,
            $encoding
        );
    }

    /**
     * Service setter
     *
     * @return \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract
     */
    public function getQRCodeService()
    {
        return $this->qrCodeService;
    }

    /**
     * Service setter
     *
     * @param QRCodeServiceContract $service
     * @return self
     */
    public function setQRCodeService($service)
    {
        $this->qrCodeService = $service;

        return $this;
    }

    /**
     * Create the QR Code service instance
     *
     * @return \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract
     */
    public function qrCodeServiceFactory($imageBackEnd = null)
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
