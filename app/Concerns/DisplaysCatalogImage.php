<?php

namespace App\Concerns;

use App\Support\SiteImage;

trait DisplaysCatalogImage
{
    abstract protected function catalogImageType(): string;

    public function displayImage(): string
    {
        return SiteImage::catalogImage($this->image ?? null, $this->catalogImageType());
    }
}
