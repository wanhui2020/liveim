<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\PlatformPayment::class, function (Faker $faker) {
    return [
        'type' => 0, // 默认银行卡
        'no' => $faker->creditCardNumber, // 支付账号
        'name' => $faker->name, // 账户名称
        'rate' => '0.005', // 费率
    ];
});
