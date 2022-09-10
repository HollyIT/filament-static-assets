<?php

use HollyIT\FilamentStaticAssets\Events\PreProcessStaticAssetsEvent;
use HollyIT\FilamentStaticAssets\FilamentStaticManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

it('successfully transforms assets', function(){
    $filament = new FilamentStaticManager();
    $filament->registerScripts(['test_script' => '/test/script/test.js']);
    Cache::set('ledecms_static_assets_buster', ['test_script' => 'bust_cache']);
    $scripts = $filament->getScripts();
    $this->assertArrayHasKey('test_script', $scripts);
    $this->assertStringContainsString('bust_cache', $scripts['test_script']);
});

it('caches assets to public', function(){
    /** @var FilamentStaticManager $filament */
    $filament = app('filament');
    $filament->registerScripts(['test_script' => __DIR__ . '/Support/test.js']);
    $this->artisan('filament:cache-assets');
    $this->assertDirectoryExists(public_path('assets'));
    $this->assertFileExists(public_path('assets') . '/app.js');
    $this->assertFileExists(public_path('assets') . '/test_script.js');
    $this->assertFileExists(public_path('assets') . '/app.css');
    $this->assertIsArray(Cache::get('ledecms_static_assets_buster', null));
    $this->assertArrayHasKey('test_script', Cache::get('ledecms_static_assets_buster', null));
});

it('transforms assets to statically cached urls', function(){
    /** @var FilamentStaticManager $filament */
    $filament = app('filament');
    $filament->registerScripts(['test_script' => __DIR__ . '/Support/test.js']);
    $this->artisan('filament:cache-assets');
    $this->assertArrayHasKey('test_script', $filament->getScripts());
    $this->assertStringContainsString('?_c=', $filament->getScripts()['test_script']);
});

it('deletes cached assets', function() {
    $this->artisan('filament:cache-assets');
    $this->assertDirectoryExists(public_path('assets'));
    $this->artisan('filament:flush-assets');
    $this->assertDirectoryDoesNotExist(public_path('assets'));
    $this->assertNull(Cache::get('ledecms_static_assets_buster', null));
});

it('can retrieve files via asset controller', function(){
    $filament = app('filament');
    $filament->registerScripts(['test_script' => __DIR__ . '/Support/test.js']);
    $this->get(route('filament.asset', ['file' => 'test_script.js']))->assertOk();
});

it('does not alter external items', function() {
    /** @var FilamentStaticManager $filament */
    $filament = app('filament');
    $filament->registerScripts(['test_script' => 'https://example.com/test.js']);
    $this->artisan('filament:cache-assets');
    $this->assertDirectoryExists(public_path('assets'));
    $this->assertIsArray(Cache::get('ledecms_static_assets_buster', null));
    $this->assertEmpty(Cache::get('ledecms_static_assets_buster', null));
    $this->assertArrayHasKey('test_script', $filament->getScripts());
    $this->assertEquals('https://example.com/test.js', $filament->getScripts()['test_script']);
});


it('can alter assets', function() {
    $filament = app('filament');
    $filament->registerScripts(['test_script' => __DIR__ . '/Support/test.js']);
    Event::listen(PreProcessStaticAssetsEvent::class, function(PreProcessStaticAssetsEvent $ev){
        $ev->files->forget('test_script');
    });

    $this->assertEmpty($filament->getScripts());
});
