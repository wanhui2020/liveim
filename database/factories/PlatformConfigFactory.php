<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\PlatformConfig::class, function (Faker $faker) {
    return [
        'entrust_start' => '09:30:00', // 委托开始时间
        'entrust_end' => '15:00:00', // 委托结束时间
        'buy_charge' => 0.003, // 买入手续费
        'sell_charge' => 0.003, // 卖出手续费
        'min_charge' => 100, // 最低充值金额
        'stop_loss' => 0.75, // 系统默认止损比例
        'min_withdraw_money' => 5, // 单笔最低提现手续费
        'min_withdraw' => 100, // 最低提现金额
        'withdraw_rate' => 0.006, // 提现手续费费率
    ];
});
