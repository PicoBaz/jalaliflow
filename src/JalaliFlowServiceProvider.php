<?php

namespace PicoBaz\JalaliFlow;

use Illuminate\Support\ServiceProvider;
use PicoBaz\JalaliFlow\Commands\JalaliHolidaysCommand;
use PicoBaz\JalaliFlow\Commands\RunJalaliEventsCommand;

class JalaliFlowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                JalaliHolidaysCommand::class,
                RunJalaliEventsCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/jalaliflow.php' => config_path('jalaliflow.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'jalaliflow');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jalaliflow.php', 'jalaliflow');

        $this->app->singleton('jalaliflow', function () {
            return new JalaliFlow();
        });
    }
}