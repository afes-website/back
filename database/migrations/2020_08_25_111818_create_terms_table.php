<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('terms', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');
            $table->timestamp('enter_scheduled_time')->useCurrent();
            $table->timestamp('exit_scheduled_time')->useCurrent();
            $table->string('color_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('terms');
    }
}
