<?php

namespace App\View\Components\Site;

use App\Support\SiteImage;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OptimizedImage extends Component
{
    /**
     * @param  list<int>|null  $widths
     */
    public function __construct(
        public string $src,
        public string $alt,
        public ?int $width = null,
        public ?int $height = null,
        public string $class = '',
        public bool $priority = false,
        public string $sizes = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
        public ?array $widths = null,
    ) {
        $this->widths ??= SiteImage::responsiveWidths();
    }

    public function assetSrc(): string
    {
        return SiteImage::url($this->src);
    }

    public function webpSrc(): ?string
    {
        return SiteImage::webpUrl($this->src);
    }

    public function webpSrcset(): string
    {
        return SiteImage::webpSrcset($this->src, $this->widths);
    }

    public function srcset(): string
    {
        return SiteImage::srcset($this->src, $this->widths);
    }

    public function render(): View|Closure|string
    {
        return view('components.site.optimized-image');
    }
}
