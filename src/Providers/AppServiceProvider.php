<?php

namespace Innoboxrr\Traits\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
        // $this->mergeConfigFrom(__DIR__ . '/../../config/innoboxrrtraits.php', 'innoboxrrtraits');
    }

    public function boot()
    {

        if ($this->app->runningInConsole())  {
            $this->publishes([__DIR__.'/../../config/innoboxrrtraits.php' => config_path('innoboxrrtraits.php')], 'config');
            $this->load(__DIR__.'/../Console/Commands');
        }

    }
    
}