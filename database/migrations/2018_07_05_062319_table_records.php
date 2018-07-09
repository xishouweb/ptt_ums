<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_records', function (Blueprint $table) {
            //
			$table->tinyInteger('gender')->default(0);
			$table->tinyInteger('age')->default(0);
			$table->tinyInteger('user_address')->default(0);
			$table->tinyInteger('industry')->default(0);
			$table->tinyInteger('hobby')->default(0);
			$table->tinyInteger('interest')->default(0);
			$table->tinyInteger('phone')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_records', function (Blueprint $table) {
            //
        });
    }
}
