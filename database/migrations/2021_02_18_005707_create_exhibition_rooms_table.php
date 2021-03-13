<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionRoomsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('exh_rooms', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');
            $table->string('room_id');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('guest_count');
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('exh_rooms');
    }
}
