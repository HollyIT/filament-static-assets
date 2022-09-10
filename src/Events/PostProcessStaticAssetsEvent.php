<?php

namespace HollyIT\FilamentStaticAssets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Collection;

class PostProcessStaticAssetsEvent
{
    use Dispatchable;
    public Collection $files;
    public string $scope;

    public function __construct(Collection $files, string $scope) {

        $this->files = $files;
        $this->scope = $scope;
    }
}
