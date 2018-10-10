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

        // Registering package commands.
        $this->commands([
            Console\Commands\HelperCarveCommand::class,
        ]);
    }
}
