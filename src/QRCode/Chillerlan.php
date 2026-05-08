<?php

namespace PragmaRX\Google2FAQRCode\QRCode;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Chillerlan implements QRCodeServiceContract
{
    /**
     * @var array<string, mixed>
     */
    protected $options = [];

    protected function getOptions(): QROptions
    {
        return new QROptions($this->buildOptionsArray());
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOptionsArray(?int $size = null): array
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

        $svg = $renderer->render($string);

        // chillerlan's render() is typed as mixed in its source, but with
        // outputInterface=QRMarkupSVG we always get a string back. Guard
        // here so PHPStan can narrow the type cleanly.
        if (!is_string($svg)) {
            throw new \RuntimeException('chillerlan returned a non-string SVG payload.');
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
