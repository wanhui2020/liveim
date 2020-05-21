<?php

use Faker\Generator as Faker;

/**
 * 填充用户账号
 * phone: 13500251234
 * password: 123456
 */
$factory->define(App\Models\CustomerUser::class, function (Faker $faker) {
    static $password;
    return [
        'agent_id' => factory(\App\Models\AgentUser::class)->create()->id,
        'agent_name' => factory(\App\Models\AgentUser::class)->create()->name,
        'phone' => $faker->phoneNumber,
        'password' => $password ?? $password = bcrypt('123456'),
        'api_token' => $faker->sha256,
        'remember_token' => str_random(10),
    ];
});
