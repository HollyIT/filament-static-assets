<?php

namespace HollyIT\FilamentStaticAssets\Command;

use Illuminate\Console\Command;
use HollyIT\FilamentStaticAssets\Command\Concerns\ClearsAssets;

class FlushCachedAssetsCommand extends Command
{
    use ClearsAssets;

    protected $signature = 'filament:flush-assets';

    protected $description = 'Removes filament assets from the public directory, forcing them to once again be served via the assets controller';


    public function handle(): int
    {
        $this->deleteAssets();
        $this->info('Cleared all filament static asset cache');

        return static::SUCCESS;
    }
}
