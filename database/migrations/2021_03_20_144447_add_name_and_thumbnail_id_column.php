<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameAndThumbnailIdColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('exh_rooms', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('thumbnail_image_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('exh_rooms', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('thumbnail_image_id');
        });
    }
}
