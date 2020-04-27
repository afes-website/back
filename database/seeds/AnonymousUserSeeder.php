<?php

use Illuminate\Database\Seeder;
use App\Models\WriterUser;
use Illuminate\Support\Facades\Hash;

class AnonymousUserSeeder extends Seeder
{
    /**
     * Add Anonymous User into writer_users table
     *
     * @return void
     */

    public function run()
    {
        WriterUser::create([
            'id' => 'anonymous',
            'name' => '名もなき麻布生',
            'password' => Hash::make('')
        ]);
    }
}
