<?php

namespace App\Http\ViewComposers;

use App\Models\ShopClassify;
use App\Models\ShopSite;
use Illuminate\Support\ServiceProvider;

class MallCompoerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('mall.*',function($view){
            $view->with('site',ShopSite::first());
            $view->with('classify',ShopClassify::where('parent_id',0)->where('status',0)->get());
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
