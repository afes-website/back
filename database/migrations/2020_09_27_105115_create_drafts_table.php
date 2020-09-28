<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exh_id');
            $table->string('content');
            $table->string('teacher_review_status');
            $table->string('review_status');
            $table->boolean('published');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drafts');
    }
}
