<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavingAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saving_awards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('saving_id');
            $table->double('amount');
            $table->double('award');
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('saving_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saving_awards');
    }
}
