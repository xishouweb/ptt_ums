<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceQueryStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_query_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('xu_host_id');
            $table->string('xu_group_id');
            $table->string('xu_robot_id');
            $table->string('symbol', 16);
            $table->integer('query_count');
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
        Schema::dropIfExists('price_query_statistics');
    }
}
