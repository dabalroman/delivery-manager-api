<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->nullable(false);
            $table->tinyInteger('amount', false, true)->nullable(false);
            $table->foreignId('address_id');
            $table->foreignId('batch_id');
            $table->foreignId('owner');
            $table->foreignId('assigned_to')->nullable(true);
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('address');
            $table->foreign('batch_id')->references('id')->on('import_batch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
