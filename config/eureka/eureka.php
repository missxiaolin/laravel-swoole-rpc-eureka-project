<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Eureka配置
    |--------------------------------------------------------------------------
    |
    |
    */
    'baseUri' => env('EUREKA_BASE_URI'),
    'appName' => env('APP_NAME', 'laravel'),
    'cacheKeyPrefix' => 'eureka:service:%s',
];