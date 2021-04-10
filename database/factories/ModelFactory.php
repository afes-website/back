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

$factory->define(App\Models\Term::class, function (Faker\Generator $faker) {
    return [
        'id'=>$faker->userName,
        'enter_scheduled_time'=>$faker->dateTimeBetween('-1year', '-1hour'),
        'exit_scheduled_time'=>$faker->dateTimeBetween('+1hour', '+1year'),
        'guest_type'=>array_rand(config('onsite.guest_types'))
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
        'exited_at'=>null,
        'exh_id'=>null,
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
        'guest_id'=>null
    ];
});

$factory->define(App\Models\ActivityLog::class, function (Faker\Generator $faker) {
    return [
        'timestamp'=>$faker->dateTime,
        'exh_id'=>$faker->userName,
        'log_type'=>$faker->randomElement(['exit','enter']),
        'guest_id'=>$faker->userName,
    ];
});
