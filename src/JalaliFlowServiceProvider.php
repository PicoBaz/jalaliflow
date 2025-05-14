<?php

namespace PicoBaz\JalaliFlow;

use Illuminate\Support\ServiceProvider;
use PicoBaz\JalaliFlow\Commands\JalaliHolidaysCommand;

class JalaliFlowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                JalaliHolidaysCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/jalaliflow.php' => config_path('jalaliflow.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jalaliflow.php', 'jalaliflow');

        $this->app->singleton('jalaliflow', function () {
            return new JalaliFlow();
        });
    }
}
