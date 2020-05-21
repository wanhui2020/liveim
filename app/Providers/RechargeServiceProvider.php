<?php

namespace App\Providers;

use App\Facades\RechargeFacade;
use App\Services\RechargeService;
use Illuminate\Support\ServiceProvider;

class RechargeServiceProvider extends ServiceProvider
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
        $this->app->bind('RechargeFacade', function () {
            return new RechargeService();
        });
    }
}
