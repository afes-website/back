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
        'name' => Str::random(8),
        'email' => $faker->email,
    ];
});

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'id' => Str::random(8),
        'name' => $faker->name,
        'password' => Hash::make($faker->password),
        "perm_admin" => false,
        "perm_blogAdmin" => false,
        "perm_blogWriter" => false,
        "perm_exhibition" => false,
        "perm_general" => false,
        "perm_reservation" => false,
        'perm_teacher' => false,
    ];
});

$factory->define(App\Models\Revision::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence(10),
        'timestamp' => $faker->dateTime(),
        'article_id' => Str::random(8),
        'content' => $faker->paragraph(),
        'status' => 'waiting',
        'handle_name' => $faker->sentence(10)
    ];
});

{
    $roles = [
        'admin' => 'perm_admin',
        'blogAdmin' => 'perm_blogAdmin',
        'blogWriter' => 'perm_blogWriter',
        'exhibition' => 'perm_exhibition',
        'general' => 'perm_general',
        'reservation' => 'perm_reservation',
        'teacher' => 'perm_teacher'
    ];

    foreach ($roles as $key => $value) {
        $factory->defineAs(App\Models\User::class, $key, function () use ($factory, $value) {

            $user = $factory->raw(App\Models\User::class);
            return array_merge($user, [$value => true]);
        });
    }
}

$factory->define(App\Models\Article::class, function (Faker\Generator $faker) {
    return [
        'id'=>Str::random(8),
        'category'=>Str::random(8),
        'title'=>$faker->sentence(10),
        'handle_name'=>$faker->sentence(10),
        'created_at'=>$faker->dateTime(),
        'updated_at'=>$faker->dateTime(),
    ];
});

$factory->define(App\Models\Exhibition::class, function (Faker\Generator $faker) {
    return [
        'id'=>Str::random(8),
        'name'=>$faker->name,
        'updated_at'=>$faker->dateTime()
    ];
});

$factory->define(App\Models\Draft::class, function (Faker\Generator $faker) {
    return [
        'content'=>$faker->paragraph(),
    ];
});

$factory->define(App\Models\Image::class, function (Faker\Generator $faker) {
    return [
        'id' => Str::random(8),
        'content' => hex2bin(
            // tiny png(1x1px 8bit)
            "89504e470d0a1a0a0000000d49484452".
            "000000010000000108000000003a7e9b".
            "550000000a4944415408d763780e0000".
            "e900e8f07b6a770000000049454e44ae".
            "426082"
        ),
        'mime_type' => 'image/png',
    ];
});
