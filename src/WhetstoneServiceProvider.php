<?php

namespace saberLiou\Whetstone;

use Illuminate\Support\ServiceProvider;

class WhetstoneServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'saberliou');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'saberliou');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/whetstone.php', 'whetstone');

        // Register the service the package provides.
        $this->app->singleton('whetstone', function ($app) {
            return new Whetstone;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['whetstone'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/whetstone.php' => config_path('whetstone.php'),
        ], 'whetstone.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/saberliou'),
        ], 'whetstone.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/saberliou'),
        ], 'whetstone.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/saberliou'),
        ], 'whetstone.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
