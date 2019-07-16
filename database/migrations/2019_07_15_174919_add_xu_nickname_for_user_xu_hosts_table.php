<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddXuNicknameForUserXuHostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_xu_hosts', function (Blueprint $table) {
            $table->string('xu_nickname', 60);
            $table->string('union_id')->nullable()->change();
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
            $table->dropColumn('xu_nickname');
            $table->string('union_id')->change();
        });
    }
}
