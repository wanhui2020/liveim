<?php

namespace App\Providers;

use App\Services\MkPayService;
use Illuminate\Support\ServiceProvider;

class MkPayServiceProvider extends ServiceProvider
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
        $this->app->bind('MkFacade', function () {
            return new MkPayService();
        });
    }
}
