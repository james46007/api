<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesCostumeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_costume', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('article_id')->unsigned();
            $table->bigInteger('costume_id')->unsigned();
            $table->timestamps();
            $table->integer('estado');

            $table->foreign('article_id')->references('id')->on('articles')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('costume_id')->references('id')->on('costumes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_costume');
    }
}
