<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');
            $table->string('email');
            $table->integer('people_count');
            $table->string('term_id');
            $table->string('name');
            $table->string('address');
            $table->string('cellphone');
            $table->string('guest_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('reservations');
    }
}
