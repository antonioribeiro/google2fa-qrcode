<?php

namespace PragmaRX\Google2FAQRCode;

use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer as BaconQrCodeWriter;
use PragmaRX\Google2FA\Google2FA as Google2FAPackage;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Google2FA extends Google2FAPackage
{
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
        return $this->getBaconQRCodeVersion() === 1
            ? $this->getQRCodeInlineV1($company, $holder, $secret, $size, $encoding)
            : $this->getQRCodeInlineV2($company, $holder, $secret, $size, $encoding);
    }

    /**
     * Generates a QR code data url to display inline for Bacon QRCode v1
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInlineV1($company, $holder, $secret, $size = 200, $encoding = 'utf-8')
    {
        $url = $this->getQRCodeUrl($company, $holder, $secret);

        $renderer = new Png();
        $renderer->setWidth($size);
        $renderer->setHeight($size);

        $bacon = new BaconQrCodeWriter($renderer);
        $data = $bacon->writeString($url, $encoding);

        return 'data:image/png;base64,'.base64_encode($data);
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
            (new RendererStyle(400))->withSize($size),
            new ImagickImageBackEnd()
        );

        $bacon = new Writer($renderer);

        $bacon->writeFile(
            $this->getQRCodeUrl($company, $holder, $secret),
            'qrcode.png'
        );

        $data = $bacon->writeString(
            $this->getQRCodeUrl($company, $holder, $secret),
            $encoding
        );

        return 'data:image/png;base64,'.base64_encode($data);
    }

    /**
     * Get Bacon QRCode current version
     *
     * @return int
     */
    public function getBaconQRCodeVersion()
    {
        return class_exists('BaconQrCode\Renderer\Image\Png') && class_exists('BaconQrCode\Writer')
            ? 1
            : 2;
    }
}
