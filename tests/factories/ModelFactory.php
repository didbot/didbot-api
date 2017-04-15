<?php

use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\Models\Tag;
use Didbot\DidbotApi\Models\Source;
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
        'text' => str_random(16),
        'geo' => new \Phaza\LaravelPostgis\Geometries\Point(34.073823, -118.239975), // Dodger Stadium
    ];
});

$factory->define(Tag::class, function (Faker\Generator $faker) {
    return [
            'text' => $faker->text($maxNbChars = 15)
    ];
});

$factory->define(\Laravel\Passport\Client::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text($maxNbChars = 15),
        'secret' => str_random(40),
        'redirect' => 'http://localhost',
        'personal_access_client' => true,
        'password_client' => false,
        'revoked' => false,
    ];
});

$factory->define(Source::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});