<?php

namespace PicoBaz\JalaliFlow\Facades;

use Illuminate\Support\Facades\Facade;

class JalaliFlow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'jalaliflow';
    }
}
