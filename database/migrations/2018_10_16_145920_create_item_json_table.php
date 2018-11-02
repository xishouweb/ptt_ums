<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemJsonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_json', function (Blueprint $table) {
            $table->increments('id');
			$table->string('k')->nullable();
			$table->string('p')->nullable();
			$table->string('dx')->nullable();
			$table->string('rt')->nullable();
			$table->string('ns')->nullable();
			$table->string('ni')->nullable();
			$table->string('v')->nullable();
			$table->string('xa')->nullable();
			$table->string('tr')->nullable();
			$table->string('mo')->nullable();
			$table->string('m0')->nullable();
			$table->string('m0a')->nullable();
			$table->string('m1')->nullable();
			$table->string('m1a')->nullable();
			$table->string('m2')->nullable();
			$table->string('m4')->nullable();
			$table->string('m5')->nullable();
			$table->string('m6')->nullable();
			$table->string('m6a')->nullable();
			$table->string('vo')->nullable();
			$table->string('o')->nullable();
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
        Schema::dropIfExists('item_json');
    }
}
