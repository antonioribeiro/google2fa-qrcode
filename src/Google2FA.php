<?php

namespace PragmaRX\Google2FAQRCode;

use PragmaRX\Google2FA\Google2FA as Google2FAPackage;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Google2FA extends Google2FAPackage
{
    /**
     * @var ImageBackEndInterface|RendererInterface|null $imageBackEnd
     */
    protected $imageBackEnd;

    public function __construct($imageBackEnd = null)
    {

        if ($imageBackEnd instanceof ImageBackEndInterface) {
            $this->imageBackEnd = $imageBackEnd;
        } else {
            $this->imageBackEnd = new SvgImageBackEnd();
        }

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
    public function getQRCodeInline($company, $holder, $secret, $size = 200, $encoding = 'utf-8')
    {
        return $this->getQRCodeInlineV2($company, $holder, $secret, $size, $encoding);
    }

    /**
     * Generates a QR code data url to display inline for Bacon QRCode v2
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInlineV2($company, $holder, $secret, $size = 200, $encoding = 'utf-8')
    {
        $renderer = new ImageRenderer(
            (new RendererStyle($size))->withSize($size),
            $this->imageBackEnd
        );

        $bacon = new Writer($renderer);

        $data = $bacon->writeString(
            $this->getQRCodeUrl($company, $holder, $secret),
            $encoding
        );

        if ($this->imageBackEnd instanceof SvgImageBackEnd) {
            return 'data:image/svg+xml;base64,'.base64_encode($data);
        }
        return $data;
    }
}
