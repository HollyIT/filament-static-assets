<?php

namespace HollyIT\FilamentStaticAssets\Command;

use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\Http\Controllers\AssetController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use HollyIT\FilamentStaticAssets\Command\Concerns\ClearsAssets;

class CacheAssetsCommand extends Command
{
    use ClearsAssets;

    protected $signature = 'filament:cache-assets';

    protected $description = 'Moves filament static assets to the public directory for direct serving';
    protected string $basePath;
    protected array $cacheKeys = [];
    /**
     * @var \filament|\filament&\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected mixed $filament;

    public function __construct()
    {
        parent::__construct();

        $this->basePath = public_path(config('filament.core_path') . '/assets');
        $this->filament = app('filament');
    }

    public function handle(): int
    {
        ServingFilament::dispatch();

        $this->deleteAssets();
        File::ensureDirectoryExists($this->basePath);
        $this->cacheCore();
        $this->cache($this->filament->getBeforeCoreScripts(true), 'js');
        $this->cache($this->filament->getScripts(true), 'js');
        $this->cache($this->filament->getStyles(true), 'css');
        Cache::set('ledecms_static_assets_buster', $this->cacheKeys);
        $this->info('Cached filament static assets');

        return static::SUCCESS;
    }

    protected function cacheCore()
    {

        $response = app(AssetController::class)('app.js');
        File::copy($response->getFile()->getRealPath(), $this->basePath . '/app.js');

        $response = app(AssetController::class)('app.css');
        File::copy($response->getFile()->getRealPath(), $this->basePath . '/app.css');
    }



    protected function cache(array $files, string $ext)
    {
        foreach ($files as $name => $path) {
            if (!Str::startsWith($path, ['http://', 'https://'])) {
                $this->cacheKeys[$name] = md5(filemtime($path));
                File::copy($path, $this->basePath . "/{$name}.{$ext}");
            }
        }
    }

}
