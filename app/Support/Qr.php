<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/** Makes QR codes as inline SVG (no Imagick/GD needed). Requires: composer require bacon/bacon-qr-code */
class Qr
{
    public static function svg(string $text, int $size = 220): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd()));
        $svg = $writer->writeString($text);

        // Drop the xml ...  header so the SVG can be embedded directly in HTML
        return preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg);
    }
}