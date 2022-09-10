<?php

namespace HollyIT\FilamentStaticAssets;

use HollyIT\FilamentStaticAssets\Command\CacheAssetsCommand;
use HollyIT\FilamentStaticAssets\Command\FlushCachedAssetsCommand;
use Illuminate\Support\ServiceProvider;

class StaticAssetsServiceProvider extends ServiceProvider
{

    public function register() {
        $this->commands([CacheAssetsCommand::class, FlushCachedAssetsCommand::class]);
        $this->app->extend('filament', function(){
            return new FilamentStaticManager();
        });

    }
}
