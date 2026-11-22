<?php

use App\Core\Providers\CoreServiceProvider;
use App\Core\Providers\ModularServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    ModularServiceProvider::class,
];
