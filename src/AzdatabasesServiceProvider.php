<?php

namespace Seumunday\Azdatabases;

use Illuminate\Support\ServiceProvider;

class AzdatabasesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'azdatabases');
        
        $this->publishes([
            __DIR__.'/config/azdatabases.php' => config_path('azdatabases.php'),
            __DIR__.'/views' => resource_path('views/vendor/azdatabases'),
            __DIR__.'/assets/js' => resource_path('assets/js/vendor'),
            __DIR__.'/commands' => app_path('Console/commands'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make('Seumunday\Azdatabases\AzdatabasesController');
    }
}
