<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHandlenameToArticleAndRevision extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('handle_name')->nullable();
        });
        Schema::table('revisions', function (Blueprint $table) {
            $table->string('handle_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('handle_name');
        });
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropColumn('handle_name');
        });
    }
}
