<?php

namespace App\Providers;

use App\Services\HyPayService;
use Illuminate\Support\ServiceProvider;

class HyPayServiceProvider extends ServiceProvider
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
        $this->app->bind('HyFacade', function () {
            return new HyPayService();
        });
    }
}
