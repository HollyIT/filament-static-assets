<?php

namespace HollyIT\FilamentStaticAssets;

use Filament\FilamentManager;
use HollyIT\FilamentStaticAssets\Events\PostProcessStaticAssetsEvent;
use HollyIT\FilamentStaticAssets\Events\PreProcessStaticAssetsEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

class FilamentStaticManager
{

    use ForwardsCalls;

    private FilamentManager $filament;

    public function __construct(FilamentManager $filament)
    {
        $this->filament = $filament;
    }

    protected ?array $assetCache = null;

    public function prepareAssets($assets, $domain): array
    {

        $extension = $domain === 'styles' ? '.css' : '.js';
        if (Request::route()?->getName() == 'filament.asset') {
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

            $file = route('filament.asset', [
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
        return $unprocessed ? $this->filament->getScripts() : $this->prepareAssets($this->filament->getScripts(), 'scripts');
    }

    public function getBeforeCoreScripts(bool $unprocessed = false): array
    {
        return $unprocessed ? $this->filament->getBeforeCoreScripts() : $this->prepareAssets($this->filament->getBeforeCoreScripts(), 'beforeCoreScripts');
    }


    public function getStyles(bool $unprocessed = false): array
    {
        return $unprocessed ? $this->filament->getStyles() : $this->prepareAssets($this->filament->getStyles(), 'styles');
    }

    public function __call($method, $parameters)
    {
        $response = $this->forwardCallTo($this->filament, $method, $parameters);

        if ($response instanceof FilamentManager) {
            return $this;
        }

        return $response;
    }
}
