<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixRelationshipTypes extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedInteger('revision_id')->change();
        });
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->unsignedInteger('draft_id')->change();
        });
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->unsignedInteger('draft_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('articles', function (Blueprint $table) {
            $table->integer('revision_id')->change();
        });
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->string('draft_id')->change();
        });
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->string('draft_id')->change();
        });
    }
}
