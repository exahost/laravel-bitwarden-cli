<?php

namespace Aleex1848\LaravelBitwardenCli\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aleex1848\LaravelBitwardenCli\LaravelBitwardenCli
 */
class LaravelBitwardenCli extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Aleex1848\LaravelBitwardenCli\LaravelBitwardenCli::class;
    }
}
