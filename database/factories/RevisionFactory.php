<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function PHPUnit\Framework\throwException;

class RevisionFactory extends Factory {

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Revision::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'title' => $this->faker->sentence(10),
            'timestamp' => $this->faker->dateTime(),
            'article_id' => Str::random(8),
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'status' => 'waiting',
            'handle_name' => $this->faker->sentence(10)
        ];
    }

    public function accept() {
        return $this->state(['status' => 'accepted']);
    }
    public function reject() {
        return $this->state(['status' => 'rejected']);
    }
}
