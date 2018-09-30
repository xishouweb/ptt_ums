<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReleaseDateAndIsTopToProtonNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proton_news', function (Blueprint $table) {
            $table->timestamp('release_date')->default(date('Y-m-d'));
            $table->tinyInteger('is_top')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proton_news', function (Blueprint $table) {
            $table->dropColumn('release_date');
            $table->dropColumn('is_top');
        });
    }
}
