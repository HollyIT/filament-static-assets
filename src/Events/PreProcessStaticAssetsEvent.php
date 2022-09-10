<?php

namespace HollyIT\FilamentStaticAssets\Events;

use Illuminate\Support\Collection;

class PreProcessStaticAssetsEvent
{

    public Collection $files;
    public string $scope;

    public function __construct(Collection $files, string $scope) {

        $this->files = $files;
        $this->scope = $scope;
    }
}
