<?php

namespace HollyIT\FilamentStaticAssets\Tests\Support;

use HollyIT\FilamentStaticAssets\FilamentStaticManager;
use Illuminate\Support\ServiceProvider;

class TestingProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('path.public', function(){
            return __DIR__  . '/../TempPublic';
        });

        $this->app->singleton('filament', FilamentStaticManager::class);
    }
}
