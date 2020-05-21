<?php

namespace App\Providers;

use App\Services\PushService;
use Illuminate\Support\ServiceProvider;

class PushServiceProvider extends ServiceProvider
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
        $this->app->bind('PushFacade', function () {
            return new PushService();
        });
    }
}
