<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Gera QR Codes em SVG (PHP puro, sem precisar de ext-gd).
 */
class Qr
{
    public static function svg(string $data, int $size = 260): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, margin: 2),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($data);
    }
}
