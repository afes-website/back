<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignConstraint extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('revision_id')->references('id')->on('revisions')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('drafts', function (Blueprint $table) {
            $table->foreign('exh_id')->references('id')->on('exhibitions')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->foreign('draft_id')->references('id')->on('drafts')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->foreign('draft_id')->references('id')->on('drafts')
                ->onUpdate('restrict')->onDelete('set null');
            $table->foreign('thumbnail_image_id')->references('id')->on('images')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('images', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('revisions', function (Blueprint $table) {
            $table->foreign('article_id')->references('id')->on('articles')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['revision_id']);
        });
        Schema::table('drafts', function (Blueprint $table) {
            $table->dropForeign(['exh_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('draft_comments', function (Blueprint $table) {
            $table->dropForeign(['draft_id']);
        });
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropForeign(['draft_id']);
            $table->dropForeign(['thumbnail_image_id']);
        });
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropForeign(['user_id']);
        });
    }
}
