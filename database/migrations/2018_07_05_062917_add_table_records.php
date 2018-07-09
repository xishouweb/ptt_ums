<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableRecords extends Migration
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
			$table->dropColumn(['summaryid']);
			$table->string('bc_id')->nullable();
			$table->tinyInteger('model')->default(0);
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
