<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('city', 64)->nullable(false);
            $table->string('street', 64)->nullable(false);
            $table->string('street_number', 8)->nullable(false);
            $table->string('flat_number', 8)->default(null);
            $table->smallInteger('floor')->default(null);
            $table->string('client_name', 64);
            $table->string('delivery_hours', 32);
            $table->string('phone', 16);
            $table->boolean('has_key')->default(false);
            $table->string('comment', 256);
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
