<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer as BaconQrCodeWriter;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Bacon implements QRCodeServiceContract
{
    /**
     * @var ImageBackEndInterface|RendererInterface|null $imageBackend
     */
    protected $imageBackend;

    /**
     * Google2FA constructor.
     *
     * @param ImageBackEndInterface|RendererInterface|null $imageBackend
     */
    public function __construct($imageBackend = null)
    {
        $this->imageBackend = $imageBackend;
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
        $renderer = new ImageRenderer(
            (new RendererStyle($size))->withSize($size),
            $this->getImageBackEnd()
        );

        $bacon = new Writer($renderer);

        $data = $bacon->writeString($string, $encoding);

        if ($this->getImageBackEnd() instanceof ImagickImageBackEnd) {
            return 'data:image/png;base64,' . base64_encode($data);
        }

        return $data;
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
     * Get image backend
     *
     * @return ImageRenderer
     */
    public function getImageBackend()
    {
        if (empty($this->imageBackend)) {
            $this->imageBackend = !$this->imagickIsAvailable()
                ? new SvgImageBackEnd()
                : new ImagickImageBackEnd();
        }

        $this->setImageBackEnd($this->imageBackend);

        return $this->imageBackend;
    }

    /**
     * Set image backend
     *
     * @param $imageBackend
     * @return $this
     */
    public function setImageBackend($imageBackend)
    {
        $this->imageBackend = $imageBackend;

        return $this;
    }
}
