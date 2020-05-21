<?php

namespace App\Providers;

use App\Services\MemberService;
use Illuminate\Support\ServiceProvider;

class MemberServiceProvider extends ServiceProvider
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
        $this->app->bind('MemberFacade', function () {
            return new MemberService();
        });
    }
}
