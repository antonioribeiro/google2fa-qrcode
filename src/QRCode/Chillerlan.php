<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Chillerlan implements QRCodeServiceContract
{
    protected $options = [];

    /**
     * Get QRCode options.
     *
     * @return \chillerlan\QRCode\QROptions
     */
    protected function getOptions()
    {
        $options = new QROptions($this->buildOptionsArray());

        return $options;
    }

    /**
     * Set QRCode options.
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Build the options array
     *
     * @param null $size
     * @return array
     */
    public function buildOptionsArray($size = null)
    {
        $defaults = [
            'version' => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_L,
        ];

        return array_merge($defaults, $this->options);
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
    public function getQRCodeInline($string, $size = null, $encoding = null)
    {
        $renderer = new QRCode($this->getOptions());

        return $renderer->render($string);
    }
}
