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

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->userName,
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
        'article_id' => $faker->userName(),
        'user_id' => $faker->userName(),
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
        'id'=>$faker->name,
        'name'=>$faker->name,
        'thumbnail_image_id'=>Str::random(8),
        'updated_at'=>$faker->dateTime()
    ];
});

$factory->define(App\Models\Draft::class, function (Faker\Generator $faker) {
    return [
        'exh_id'=>$faker->userName(),
        'content'=>$faker->paragraph(),
        'user_id'=>$faker->userName()
    ];
});

$factory->define(App\Models\Term::class, function (Faker\Generator $faker) {
    return [
        'id'=>$faker->userName,
        'enter_scheduled_time'=>$faker->dateTime,
        'exit_scheduled_time'=>($faker->dateTime+$faker->dateTime),
        'guest_type'=>key(array_rand(config('onsite.guest_types')))
    ];
});

$factory->define(App\Models\ExhibitionRoom::class, function (Faker\Generator $faker) {
    $capacity = $faker->numberBetween(1);
    return [
        'id'=>$faker->userName,
        'room_id'=>$faker->userName,
        'capacity'=>$capacity,
        'guest_count'=>$faker->numberBetween(0,$capacity-1),
        'updated_at'=>$faker->dateTime,
    ];
});

$factory->define(App\Models\Guest::class, function (Faker\Generator $faker) {
    return [
        'id'=>$faker->userName,
        'entered_at'=>$faker->dateTime,
        'exited_at'=>$faker->dateTime,
        'exh_id'=>$faker->userName,
        'term_id'=>$faker->userName,
        'reservation_id'=>$faker->userName,
    ];
});

$factory->define(App\Models\Reservation::class, function (Faker\Generator $faker) {
    return [
        'id'=>$faker->userName,
        'people_count'=>$faker->numberBetween(1),
        'name'=>$faker->name,
        'term_id'=>$faker->userName,
        'email'=>$faker->email,
        'address'=>$faker->address,
        'cellphone'=>$faker->phoneNumber,
        'guest_id'=>$faker->userName,
    ];
});

$factory->define(App\Models\ActivityLog::class, function (Faker\Generator $faker) {
    return [
        'id'=>$faker->userName,
        'timestamp'=>$faker->dateTime,
        'exh_id'=>$faker->userName,
        'log_type'=>array_rand(['enter','exit']),
        'guest_id'=>$faker->userName,
    ];
});
