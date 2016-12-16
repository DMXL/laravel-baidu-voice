<?php

namespace Dmxl\LaravelBaiduVoice;

use Illuminate\Support\ServiceProvider;

class LaravelBaiduVoiceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('baiduvoice.php'),
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'baiduvoice'
        );

        $this->app->singleton([LaravelBaiduVoice::class => 'baiduvoice'], function($app) {
            return new LaravelBaiduVoice(config('baiduvoice'));
        });
    }
}