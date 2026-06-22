<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Support\SiteImage;
use App\Support\StorageImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('site image fallback src prefers smallest webp variant', function (): void {
    $directory = 'images/site/test-fallback';
    $relativePath = "{$directory}/sample.jpg";
    $basePath = public_path($relativePath);

    File::ensureDirectoryExists(dirname($basePath));
    File::put($basePath, 'original');

    File::put(public_path(SiteImage::variantPath($relativePath, 640)), 'variant-640');
    File::put(
        public_path(SiteImage::replaceExtension(SiteImage::variantPath($relativePath, 640), 'webp')),
        'webp-640',
    );
    File::put(public_path(SiteImage::variantPath($relativePath, 1024)), 'variant-1024');

    expect(SiteImage::fallbackSrc($relativePath, [640, 1024]))
        ->toContain('sample-640w.webp')
        ->not->toContain('sample.jpg');

    File::deleteDirectory(public_path($directory));
});

test('site image builds responsive srcset and webp srcset', function (): void {
    $directory = 'images/site/test-srcset';
    $relativePath = "{$directory}/sample.jpg";
    $basePath = public_path($relativePath);

    File::ensureDirectoryExists(dirname($basePath));
    File::put($basePath, 'original');

    foreach ([640, 1024] as $width) {
        File::put(public_path(SiteImage::variantPath($relativePath, $width)), "variant-{$width}");
        File::put(
            public_path(SiteImage::replaceExtension(SiteImage::variantPath($relativePath, $width), 'webp')),
            "webp-{$width}",
        );
    }

    File::put(public_path(SiteImage::replaceExtension($relativePath, 'webp')), 'base-webp');

    $srcset = SiteImage::srcset($relativePath, [640, 1024, 1280]);
    $webpSrcset = SiteImage::webpSrcset($relativePath, [640, 1024, 1280]);

    expect($srcset)->toContain('sample-640w.jpg 640w')
        ->and($srcset)->toContain('sample-1024w.jpg 1024w')
        ->and($webpSrcset)->toContain('sample-640w.webp 640w')
        ->and($webpSrcset)->toContain('sample-1024w.webp 1024w');

    File::deleteDirectory(public_path($directory));
});

test('storage image optimizer generates responsive variants', function (): void {
    if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
        $this->markTestSkipped('GD with WebP support is required.');
    }

    $relativePath = 'menu-items/test-optimizer.jpg';
    $sourcePath = SiteImage::diskPath($relativePath);

    File::ensureDirectoryExists(dirname($sourcePath));

    $image = imagecreatetruecolor(1600, 1200);
    $background = imagecolorallocate($image, 220, 40, 40);
    imagefilledrectangle($image, 0, 0, 1600, 1200, $background);
    imagejpeg($image, $sourcePath, 90);
    imagedestroy($image);

    StorageImageOptimizer::optimize($relativePath);

    expect(File::exists(SiteImage::diskPath(SiteImage::replaceExtension($relativePath, 'webp'))))->toBeTrue()
        ->and(File::exists(SiteImage::diskPath(SiteImage::variantPath($relativePath, 640))))->toBeTrue()
        ->and(File::exists(SiteImage::diskPath(SiteImage::replaceExtension(SiteImage::variantPath($relativePath, 640), 'webp'))))->toBeTrue();

    StorageImageOptimizer::deleteVariants($relativePath);
    File::delete($sourcePath);
})->group('gd');

test('menu item image upload triggers storage optimization', function (): void {
    if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
        $this->markTestSkipped('GD with WebP support is required.');
    }

    $relativePath = 'menu-items/test-model.jpg';
    $sourcePath = SiteImage::diskPath($relativePath);

    File::ensureDirectoryExists(dirname($sourcePath));

    $image = imagecreatetruecolor(800, 600);
    $background = imagecolorallocate($image, 20, 120, 220);
    imagefilledrectangle($image, 0, 0, 800, 600, $background);
    imagejpeg($image, $sourcePath, 90);
    imagedestroy($image);

    $category = Category::query()->create([
        'name' => 'Test',
        'slug' => 'test-'.uniqid(),
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    $item = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Test Image Item',
        'price' => 500,
        'image' => $relativePath,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    expect(File::exists(SiteImage::diskPath(SiteImage::variantPath($relativePath, 640))))->toBeTrue();

    $item->delete();
    StorageImageOptimizer::deleteVariants($relativePath);
    File::delete($sourcePath);
})->group('gd');
