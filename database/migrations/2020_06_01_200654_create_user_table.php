<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('email', 64);
            $table->timestamps();
        });

        Schema::table('import_batch', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user');
        });

        DB::table('user')->insert(
            ['name' => 'dev', 'email' => 'dabalroman@gmail.com']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
