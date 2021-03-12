<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TransferUsersRecord extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("
            INSERT INTO users (
                id,
                name,
                password,
                perm_admin,
                perm_blogAdmin,
                perm_blogWriter,
                perm_exhibition,
                perm_general,
                perm_reservation
            )
            SELECT id, name, password, 0, 0, 1, 0, 0, 0 FROM writer_users;");
        DB::statement("
            update users
            set perm_blogAdmin = 1
            where exists(
                select * from admin_users where admin_users.id = users.id
            )");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
//        throw new Error("rollback is not supported");
    }
}
