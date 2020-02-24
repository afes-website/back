<?php

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

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(App\Models\AdminUser::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->userName,
        'name' => $faker->name,
        'password' => Hash::make($faker->password),
    ];
});

$factory->define(App\Models\WriterUser::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->userName,
        'name' => $faker->name,
        'password' => Hash::make($faker->password),
    ];
});

$factory->define(App\Models\Revision::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence(10),
        'timestamp' => $faker->dateTime(),
        'article_id' => $faker->userName(),
        'user_id' => $faker->userName(),
        'content' => $faker->paragraph(),
        'status' => 'waiting',
    ];
});

$factory->define(App\Models\Article::class, function (Faker\Generator $faker) {
    return [
        'id'=>Str::random(8),
        'category'=>Str::random(8),
        'title'=>$faker->sentence(10),
        'created_at'=>$faker->dateTime(),
        'updated_at'=>$faker->dateTime(),
    ];
});
