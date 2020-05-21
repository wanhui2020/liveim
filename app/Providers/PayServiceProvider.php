<?php

namespace App\Providers;

use App\Services\PayService;
use Illuminate\Support\ServiceProvider;

class PayServiceProvider extends ServiceProvider
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
        $this->app->bind('PayFacade', function () {
            return new PayService();
        });
    }
}
