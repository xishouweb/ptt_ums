<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->boolean('verified');
            $table->boolean('enabled');
            $table->string('_id');
            $table->string('address');
            $table->string('symbol');
            $table->integer('decimals');
            $table->string('totalSupply');
            $table->string('name');
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
        Schema::dropIfExists('contracts');
    }
}
