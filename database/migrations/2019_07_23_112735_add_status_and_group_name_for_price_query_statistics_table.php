<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAndGroupNameForPriceQueryStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_query_statistics', function (Blueprint $table) {
            $table->string('xu_group_name')->nullable();
            $table->tinyInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_query_statistics', function (Blueprint $table) {
            $table->dropColumn('xu_group_name');
            $table->dropColumn('status');
        });
    }
}
