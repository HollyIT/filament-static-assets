<?php

namespace HollyIT\FilamentStaticAssets;

use Filament\FilamentManager;
use HollyIT\FilamentStaticAssets\Events\PostProcessStaticAssetsEvent;
use HollyIT\FilamentStaticAssets\Events\PreProcessStaticAssetsEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class FilamentStaticManager extends FilamentManager
{

    protected ?array $assetCache = null;

    public function prepareAssets($assets, $domain): array
    {
        $extension = $domain === 'styles' ? '.css' : '.js';
        if (Request::route()?->getName() == 'filament.assets') {
            return $assets;
        }

        if (!$this->assetCache) {
            $this->assetCache = Cache::get('ledecms_static_assets_buster', []);
        }
        $assets = collect($assets);

        event(new PreProcessStaticAssetsEvent($assets, $domain));
        $files = $assets->map(function ($path, $name) use ($extension) {
            if (Str::startsWith(strtolower($path), ['http://', 'https://'])) {
                return $path;
            }

            $file = route('filament.assets', [
                'file' => $name .  $extension
            ]);
            if (isset($this->assetCache[$name])) {
                $file.=(str_contains($file,'?') ? '&' : '?' ) . '_c=' . $this->assetCache[$name];
            }
            return $file;

        });

        event(new PostProcessStaticAssetsEvent($files, $domain));
        return $files->toArray();
    }

    public function getScripts(bool $unprocessed = false): array
    {
        return $unprocessed ? $this->scripts : $this->prepareAssets($this->scripts, 'scripts');
    }

    public function getBeforeCoreScripts(bool $unprocessed = false): array
    {
        return $unprocessed ? $this->beforeCoreScripts : $this->prepareAssets($this->beforeCoreScripts, 'beforeCoreScripts');
    }


    public function getStyles(bool $unprocessed = false): array
    {
        return $unprocessed ? $this->styles : $this->prepareAssets($this->styles, 'styles');
    }
}
