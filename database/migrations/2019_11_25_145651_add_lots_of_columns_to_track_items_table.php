<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLotsOfColumnsToTrackItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('track_items', function (Blueprint $table) {
            $table->tinyInteger('gender')->default(0);
            $table->tinyInteger('age')->default(0);
            $table->tinyInteger('user_address')->default(0);
            $table->tinyInteger('industry')->default(0);
            $table->tinyInteger('hobby')->default(0);
            $table->tinyInteger('interest')->default(0);
            $table->tinyInteger('phone')->default(0);
            $table->tinyInteger('model')->default(0);
            $table->integer('UID')->default(0);
            $table->tinyInteger('type')->default(0);
            $table->index('UID');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('track_items', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('age');
            $table->dropColumn('user_address');
            $table->dropColumn('industry');
            $table->dropColumn('hobby');
            $table->dropColumn('interest');
            $table->dropColumn('phone');
            $table->dropColumn('model');
            $table->dropColumn('UID');
            $table->dropColumn('type');
            $table->dropIndex('UID');
            $table->dropIndex('user_id');
        });
    }
}
