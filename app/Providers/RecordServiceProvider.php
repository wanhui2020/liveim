<?php

namespace App\Providers;

use App\Services\RechargeService;
use App\Services\RecordService;
use Illuminate\Support\ServiceProvider;

class RecordServiceProvider extends ServiceProvider
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
        $this->app->bind('RecordFacade', function () {
            return new RecordService();
        });
    }
}
