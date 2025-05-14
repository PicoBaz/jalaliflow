<?php

namespace PicoBaz\JalaliFlow\Traits;

use PicoBaz\JalaliFlow\Facades\JalaliFlow;

trait JalaliDate
{
    public function getJalaliDateAttribute()
    {
        return JalaliFlow::toJalali($this->created_at->format('Y-m-d'));
    }
}
