<?php

namespace HollyIT\FilamentStaticAssets\Command\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait ClearsAssets
{
    protected function deleteAssets(): void
    {
        $path = public_path(config('filament.core_path') . '/assets');
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
        Cache::forget('ledecms_static_assets_buster');
    }
}
