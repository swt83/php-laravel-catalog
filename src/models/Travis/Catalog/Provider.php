<?php

namespace Travis\Catalog;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // set root
        $dir = __DIR__.'/../../../';

        // publish migrations
        $this->publishes([
            $dir.'migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}