<?php

namespace App\Providers;

use App\Services\HcPayService;
use Illuminate\Support\ServiceProvider;

class HcPayServiceProvider extends ServiceProvider
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
        $this->app->bind('HcFacade', function () {
            return new HcPayService();
        });
    }
}
