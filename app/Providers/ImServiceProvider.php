<?php

namespace App\Providers;

use App\Services\ImService;
use Illuminate\Support\ServiceProvider;

class ImServiceProvider extends ServiceProvider
{
    /**
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ImFacade', function () {
            return new ImService();
        });
    }
}
