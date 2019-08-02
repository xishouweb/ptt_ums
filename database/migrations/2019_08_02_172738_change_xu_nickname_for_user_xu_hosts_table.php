<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeXuNicknameForUserXuHostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_xu_hosts', function (Blueprint $table) {
            $table->string('xu_nickname', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_xu_hosts', function (Blueprint $table) {
            $table->string('xu_nickname', 60)->change();
        });
    }
}
