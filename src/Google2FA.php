<?php

namespace PragmaRX\Google2FAQRCode;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer;
use chillerlan\QRCode\QRCode;
use PragmaRX\Google2FA\Google2FA as Google2FAPackage;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract;

class Google2FA extends Google2FAPackage
{
    /**
     * @var \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract $qrCodeService
     */
    protected $qrCodeService;

    /**
     * Google2FA constructor.
     *
     * @param \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract|null $qrCodeService
     */
    public function __construct(?QRCodeServiceContract $qrCodeService = null)
    {
        $this->setQrCodeService(
            empty($qrCodeService)
                ? $this->qrCodeServiceFactory()
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
        if (empty($this->getQrCodeService())) {
            throw new MissingQrCodeServiceException(
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
    public function getQrCodeService()
    {
        return $this->qrCodeService;
    }

    /**
     * Service setter
     *
     * @param \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract $service
     * @return self
     */
    public function setQrCodeService($service)
    {
        $this->qrCodeService = $service;

        return $this;
    }

    /**
     * Create the QR Code service instance
     *
     * @return \PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract
     */
    public function qrCodeServiceFactory()
    {
        if (
            class_exists(Writer::class) ||
            class_exists(ImageRenderer::class)
        ) {
            return new Bacon();
        }

        if (class_exists(QRCode::class)) {
            return new Chillerlan();
        }

        return null;
    }
}
