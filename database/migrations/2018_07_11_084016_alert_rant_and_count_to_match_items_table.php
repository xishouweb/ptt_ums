<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertRantAndCountToMatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('match_items', function (Blueprint $table) {
            $table->float('rant', 8, 2)->default(0)->nullable()->change();
            $table->integer('count')->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match_items', function (Blueprint $table) {
            $table->float('rant', 8, 2)->change();
            $table->integer('count')->default(0)->change();
        });
    }
}
