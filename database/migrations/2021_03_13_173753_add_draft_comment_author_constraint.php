<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDraftCommentAuthorConstraint extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        //
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
        });
        //
    }
}
