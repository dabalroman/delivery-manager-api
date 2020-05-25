<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportBatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_batch', function (Blueprint $table) {
            $table->id();
            $table->string('source', 64)->nullable(false);
            $table->dateTime('import_date')->nullable(false);
            $table->smallInteger('address_amount');
            $table->smallInteger('orders_amount');
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
        Schema::dropIfExists('import_batch');
    }
}
