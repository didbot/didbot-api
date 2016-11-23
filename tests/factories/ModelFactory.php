<?php

use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\Models\Tag;
use Didbot\DidbotApi\Test\Models\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(Did::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'text' => str_random(16)
    ];
});

$factory->define(Tag::class, function (Faker\Generator $faker) {
    return [
            'user_id' => 1,
            'text' => $faker->text($maxNbChars = 15)
    ];
});