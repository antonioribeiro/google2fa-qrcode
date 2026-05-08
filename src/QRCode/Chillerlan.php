<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRMarkupSVG;
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
            'version' => Version::AUTO,
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::L,
        ];

        $options = array_merge($defaults, $this->options);

        // Let this package handle the base64 wrap so output is deterministic
        // regardless of chillerlan's per-version defaults. See PR #12.
        $options['outputBase64'] = false;

        return $options;
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

        return 'data:image/svg+xml;base64,' . base64_encode($renderer->render($string));
    }
}
