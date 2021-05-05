<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('invoice_id')->unsigned();
            $table->bigInteger('article_id')->unsigned();
            $table->integer('quantity');
            $table->double('val_uni');
            $table->double('val_tot');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoice')
                ->onUpdate('cascade');
            $table->foreign('article_id')->references('id')->on('articles')
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
        Schema::dropIfExists('invoice_detail');
    }
}
