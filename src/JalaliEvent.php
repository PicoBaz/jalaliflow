<?php

namespace PicoBaz\JalaliFlow;

use Illuminate\Database\Eloquent\Model;

class JalaliEvent extends Model
{
    protected $fillable = ['name', 'frequency', 'start_date', 'next_run', 'action'];

    protected $casts = [
        'frequency' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}