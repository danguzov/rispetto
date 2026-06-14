<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RispettoQrCode
{
    public const TYPE_MENU = 'menu';

    public const TYPE_INSTAGRAM = 'instagram';

    public const TYPE_FACEBOOK = 'facebook';

    /**
     * @return array<string, array{label: string, description: string, filename: string, url: string}>
     */
    public static function definitions(): array
    {
        return [
            self::TYPE_MENU => [
                'label' => 'Jelovnik',
                'description' => 'Vodi na digitalni jelovnik na sajtu.',
                'filename' => 'qr-jelovnik-rispetto.svg',
                'url' => route('menu', absolute: true),
            ],
            self::TYPE_INSTAGRAM => [
                'label' => 'Instagram',
                'description' => 'Vodi direktno na Instagram profil.',
                'filename' => 'qr-instagram-rispetto.svg',
                'url' => self::socialUrl('instagram'),
            ],
            self::TYPE_FACEBOOK => [
                'label' => 'Facebook',
                'description' => 'Vodi direktno na Facebook stranicu.',
                'filename' => 'qr-facebook-rispetto.svg',
                'url' => self::socialUrl('facebook'),
            ],
        ];
    }

    public static function generate(string $type): string
    {
        $definition = self::definition($type);
        $url = $definition['url'];

        if (blank($url) || $url === '#') {
            throw new InvalidArgumentException("URL za QR kod [{$type}] nije podešen u config/site.php.");
        }

        /** @var array{0: int, 1: int, 2: int} $background */
        $background = config('site.qr.colors.background', [5, 5, 5]);
        /** @var array{0: int, 1: int, 2: int} $foreground */
        $foreground = config('site.qr.colors.foreground', [0, 255, 3]);

        $fill = Fill::uniformColor(
            new Rgb($background[0], $background[1], $background[2]),
            new Rgb($foreground[0], $foreground[1], $foreground[2]),
        );

        $renderer = new ImageRenderer(
            new RendererStyle(
                (int) config('site.qr.size', 400),
                (int) config('site.qr.margin', 1),
                null,
                null,
                $fill,
            ),
            new SvgImageBackEnd,
        );

        $writer = new Writer($renderer);

        return $writer->writeString(
            $url,
            encoding: \BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ENCODING,
            ecLevel: ErrorCorrectionLevel::H(),
        );
    }

    public static function downloadResponse(string $type): StreamedResponse
    {
        $definition = self::definition($type);
        $svg = self::generate($type);

        return response()->streamDownload(
            static function () use ($svg): void {
                echo $svg;
            },
            $definition['filename'],
            [
                'Content-Type' => 'image/svg+xml',
            ],
        );
    }

    public static function isConfigured(string $type): bool
    {
        try {
            $url = self::definition($type)['url'];

            return filled($url) && $url !== '#';
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @return array{label: string, description: string, filename: string, url: string}
     */
    private static function definition(string $type): array
    {
        $definitions = self::definitions();

        if (! array_key_exists($type, $definitions)) {
            throw new InvalidArgumentException("Nepoznat tip QR koda: {$type}");
        }

        return $definitions[$type];
    }

    private static function socialUrl(string $icon): string
    {
        /** @var array<int, array{label: string, icon: string, url: string}> $socials */
        $socials = config('site.social', []);

        $url = Arr::first($socials, fn (array $social): bool => ($social['icon'] ?? '') === $icon)['url'] ?? '#';

        return $url;
    }
}
