<?php

use Illuminate\Database\Seeder;
use App\Models\User;
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
        if(User::find('anonymous'))return;
        User::create([
            'id' => 'anonymous',
            'name' => '名もなき麻布生',
            'password' => Hash::make(''),
            'perm_admin' => false,
            'perm_blogAdmin' => false,
            'perm_blogWriter' => true,
            'perm_exhibition' => false,
            'perm_general' => false,
            'perm_reservation' => false,
        ]);
    }
}
