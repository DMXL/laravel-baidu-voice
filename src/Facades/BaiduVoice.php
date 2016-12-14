<?php

namespace Dmxl\LaravelBaiduVoice\Facades;

use Illuminate\Support\Facades\Facade;

class BaiduVoice extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'baiduvoice';
    }
}