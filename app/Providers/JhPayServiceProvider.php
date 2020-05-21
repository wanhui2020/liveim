<?php

namespace App\Providers;

use App\Services\JhPayService;
use Illuminate\Support\ServiceProvider;

class JhPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('JhFacade', function () {
            return new JhPayService();
        });
    }
}
