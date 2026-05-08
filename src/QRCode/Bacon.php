<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Bacon implements QRCodeServiceContract
{
    /**
     * @var ImageBackEndInterface|null
     */
    protected $imageBackEnd;

    public function __construct(?ImageBackEndInterface $imageBackEnd = null)
    {
        $this->imageBackEnd = $imageBackEnd;
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
            $this->getImageBackend()
        );

        $bacon = new Writer($renderer);

        $data = $bacon->writeString($string, $encoding);

        if ($this->getImageBackend() instanceof ImagickImageBackEnd) {
            return 'data:image/png;base64,' . base64_encode($data);
        }

        if ($this->getImageBackend() instanceof SvgImageBackEnd) {
            return 'data:image/svg+xml;base64,' . base64_encode($data);
        }

        return $data;
    }

    public function imagickIsAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    public function getImageBackend(): ImageBackEndInterface
    {
        if ($this->imageBackEnd === null) {
            $this->imageBackEnd = $this->imagickIsAvailable()
                ? new ImagickImageBackEnd()
                : new SvgImageBackEnd();
        }

        return $this->imageBackEnd;
    }

    public function setImageBackend(ImageBackEndInterface $imageBackEnd): self
    {
        $this->imageBackEnd = $imageBackEnd;

        return $this;
    }
}
