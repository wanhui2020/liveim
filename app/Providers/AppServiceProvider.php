<?php

namespace App\Providers;

use App\Models\FinanceRecharge;
use App\Models\FinanceWithdraw;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 自定义资金流水表存储关联模型类型
        Relation::morphMap([
            '充值' => FinanceRecharge::class,
            '提现' => FinanceWithdraw::class,
        ]);
    }
}
