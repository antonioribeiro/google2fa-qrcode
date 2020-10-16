<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer as BaconQrCodeWriter;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Bacon implements QRCodeServiceContract
{
    /**
     * @var ImageBackEndInterface|RendererInterface|null $imageBackEnd
     */
    protected $imageBackEnd;

    /**
     * Google2FA constructor.
     *
     * @param ImageBackEndInterface|RendererInterface|null $imageBackEnd
     */
    public function __construct($imageBackEnd = null)
    {
        $this->instantiate($imageBackEnd);
    }

    /**
     * Generates a QR code data url to display inline.
     *
     * @param string $string
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInline($string, $size = 200, $encoding = 'utf-8')
    {
        return $this->getBaconQRCodeVersion() === 1
            ? $this->getQRCodeInlineV1($string, $size, $encoding)
            : $this->getQRCodeInlineV2($string, $size, $encoding);
    }

    /**
     * Generates a QR code data url to display inline for Bacon QRCode v1
     *
     * @param string $string
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInlineV1($string, $size = 200, $encoding = 'utf-8')
    {
        $renderer = $this->imageBackEnd;
        $renderer->setWidth($size);
        $renderer->setHeight($size);

        $bacon = new BaconQrCodeWriter($renderer);
        $data = $bacon->writeString($string, $encoding);

        if ($this->imageBackEnd instanceof Png) {
            return 'data:image/png;base64,' . base64_encode($data);
        }

        return $data;
    }

    /**
     * Generates a QR code data url to display inline for Bacon QRCode v2
     *
     * @param string $string
     * @param int    $size
     * @param string $encoding Default to UTF-8
     *
     * @return string
     */
    public function getQRCodeInlineV2($string, $size = 200, $encoding = 'utf-8')
    {
        $renderer = new ImageRenderer(
            (new RendererStyle($size))->withSize($size),
            $this->imageBackEnd
        );

        $bacon = new Writer($renderer);

        $data = $bacon->writeString($string, $encoding);

        if ($this->imageBackEnd instanceof ImagickImageBackEnd) {
            return 'data:image/png;base64,' . base64_encode($data);
        }

        return $data;
    }

    /**
     * Get Bacon QRCode current version
     *
     * @return int
     */
    public function getBaconQRCodeVersion()
    {
        return class_exists('BaconQrCode\Renderer\Image\Png') &&
            class_exists('BaconQrCode\Writer')
            ? 1
            : 2;
    }

    /**
     * Check if Imagick is available
     *
     * @return int
     */
    public function imagickIsAvailable()
    {
        return extension_loaded('imagick');
    }

    /**
     * @param \BaconQrCode\Renderer\Image\ImageBackEndInterface|null $imageBackEnd
     */
    protected function instantiate(?ImageBackEndInterface $imageBackEnd): void
    {
        if (!$this->imagickIsAvailable()) {
            return;
        }

        if ($this->getBaconQRCodeVersion() === 1) {
            if ($imageBackEnd instanceof RendererInterface) {
                $this->imageBackEnd = $imageBackEnd;
            } else {
                $this->imageBackEnd = new Png();
            }
        } else {
            if ($imageBackEnd instanceof ImageBackEndInterface) {
                $this->imageBackEnd = $imageBackEnd;
            } else {
                $this->imageBackEnd = new ImagickImageBackEnd();
            }
        }
    }
}
