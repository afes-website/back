<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SetTimestampDefaultValueArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            if(env('DB_CONNECTION') == 'mysql') {
                DB::statement('ALTER TABLE articles CHANGE created_at created_at timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\'');
                DB::statement('ALTER TABLE articles CHANGE created_at created_at timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\'');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            if(env('DB_CONNECTION') == 'mysql') {
                DB::statement('ALTER TABLE articles CHANGE created_at created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
                DB::statement('ALTER TABLE articles CHANGE created_at created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
            }
        });
    }
}
