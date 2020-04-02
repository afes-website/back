<?php

use Illuminate\Database\Seeder;

class AnonymousUserSeeder extends Seeder
{
    /**
     * Add Anonymous User into writer_users table
     *
     * @return void
     */

    public function run()
    {
        DB::table('writer_users')->insert([
            'id' => 'anonymous',
            'name' => '名もなき麻布生',
            'password' => '$2y$10$ghrvYmAq70vrNEDfjlqGTe8f5t10O8Qxj2BtUH3UuvrcOu8topsgC' // empty
        ]);
    }
}
