<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');
            $table->string('category');
            $table->string('title');
            $table->string('revision_id');
            $table->string('created_at');
            $table->timestamp('updated_at');
            // 'id', 'category', 'title', 'revision_id', 'created_at', 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
