<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('article_id')->unsigned();
            $table->date('date');
            $table->string('description');
            $table->integer('entrada');
            $table->double('ent_val_uni');
            $table->double('ent_val_tot');
            $table->integer('salida');
            $table->double('sal_val_uni');
            $table->double('sal_val_tot');
            $table->integer('existe');
            $table->double('exi_val_uni');
            $table->double('exi_val_tot');
            $table->string('estado');
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles')
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
        Schema::dropIfExists('inventory');
    }
}
