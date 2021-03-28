<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->call('AnonymousUserSeeder');
        // $this->call('UsersTableSeeder');
    }
}
