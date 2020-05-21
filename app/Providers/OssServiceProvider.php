<?php

namespace App\Providers;

use App\Service\OssService;
use Illuminate\Support\ServiceProvider;

class OssServiceProvider extends ServiceProvider
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
        $this->app->bind('OssFacade', function () {
            return new OssService();
        });
    }
}
