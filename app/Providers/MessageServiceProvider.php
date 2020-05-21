<?php

namespace App\Providers;

use App\Facades\EasySmsFacade;
use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class MessageServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 这里将easySms注册为一个单例，并且添加门面访问
        $this->app->singleton('easySms', function () {
            $config = config('easysms');
            return new EasySms($config);
        });
    }
}
