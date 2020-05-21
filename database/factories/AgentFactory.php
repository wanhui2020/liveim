<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\AgentUser::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->freeEmail,
        'phone' => $faker->phoneNumber,
        'password' =>bcrypt('123456'),
    ];
});
