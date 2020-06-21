<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route', function (Blueprint $table) {
            $table->id();
            $table->string('addresses_ids')->nullable(false);
            $table->char('id_hash', 32);
            $table->char('routed_hash', 32);
            $table->foreignId('courier_id');
            $table->foreignId('batch_id')->nullable(false);
            $table->timestamps();

            $table->foreign('courier_id')->references('id')->on('courier');
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
        Schema::dropIfExists('route');
    }
}
