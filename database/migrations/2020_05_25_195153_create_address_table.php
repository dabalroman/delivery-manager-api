<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAddressTable
 */
class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->string('city', 64);
            $table->string('street', 64);
            $table->string('street_number', 8);
            $table->string('flat_number', 8)->nullable();
            $table->smallInteger('floor')->nullable();
            $table->string('client_name', 64)->nullable();
            $table->string('delivery_hours', 32)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('code', 32)->nullable();
            $table->string('comment', 512)->nullable();
            $table->string('geo_cord', 64)->nullable();
            $table->char('id_hash', 32);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address');
    }
}
