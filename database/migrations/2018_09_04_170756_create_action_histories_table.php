<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('type', 60)->nullable();
            $table->string('action', 60)->nullable();
            $table->integer('data')->nullable();
            $table->string('note')->nullable();
            $table->string('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_histories');
    }
}
