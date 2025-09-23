<?php

namespace PragmaRX\Google2FAQRCode;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Renderer\ImageRenderer;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use PragmaRX\Google2FAQRCode\QRCode\QRCodeServiceContract;
use BaconQrCode\Renderer\Image\RendererInterface;
use BaconQrCode\Writer as BaconQrCodeWriter;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use PragmaRX\Google2FA\Google2FA as Google2FAPackage;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQRCodeServiceException;

class Google2FA extends Google2FAPackage
{
    /**
     * @var ImageBackEndInterface|RendererInterface|null $imageBackEnd
     */
    protected $qrCodeService;

    /**
     * Google2FA constructor.
     *
     * @param QRCodeServiceContract|null $qrCodeService
     * @param ImageBackEndInterface|RendererInterface|null $imageBackEnd
     */
    public function __construct($qrCodeService = null, $imageBackEnd = null)
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
            class_exists('BaconQrCode\Writer') &&
            class_exists('BaconQrCode\Renderer\ImageRenderer')
        ) {
            return new Bacon($imageBackEnd);
        }

        if (class_exists('chillerlan\QRCode\QRCode')) {
            return new Chillerlan();
        }

        return null;
    }
}
