<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id');
            $table->foreignId('batch_id')->nullable(false);
            $table->foreignId('route_id')->nullable(false);
            $table->timestamps();

            $table->foreign('courier_id')->references('id')->on('courier');
            $table->foreign('batch_id')->references('id')->on('import_batch');
            $table->foreign('route_id')->references('id')->on('route');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_routes');
    }
}
