<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandImagesBlob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('images', function (Blueprint $table) {

        // });
        if(env('DB_CONNECTION') == 'mysql')
            DB::statement('ALTER TABLE images MODIFY content LONGBLOB NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('images', function (Blueprint $table) {
        //     //
        // });
        if(env('DB_CONNECTION') == 'mysql')
            DB::statement('ALTER TABLE images MODIFY content BLOB NOT NULL;');
    }
}
